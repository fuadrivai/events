<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\GoogleSheetExport;
use App\Models\EventRegistration;
use App\Services\GoogleSheetsExporter;
use Illuminate\Http\Request;

class EventRegistrationGoogleSheetExportController extends Controller
{
    public function export(Request $request, string $eventId, GoogleSheetsExporter $gs)
    {
        $user = $request->user();
        abort_unless($user && $user->email, 403, 'User email not found.');

        $event = Event::where('id', $eventId)->firstOrFail();

        $fields = $event->fields()
            ->orderBy('order_index')
            ->get(['field_key', 'label']);

        $header = array_merge(
            ['ID','Created At','Name','Email','Phone','Status','Package','Amount'],
            $fields->map(fn($f) => $f->label ?: $f->field_key)->toArray()
        );

        $export = GoogleSheetExport::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$export) {
            $spreadsheetId = $gs->createSpreadsheet('Registrations - '.$event->title.' - '.$user->email);
            $sheetId = $gs->getFirstSheetId($spreadsheetId);
            $gs->writeHeader($spreadsheetId, $header);
            $gs->shareToEmail($spreadsheetId, $user->email, 'writer');

            $export = GoogleSheetExport::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'spreadsheet_id' => $spreadsheetId,
                'spreadsheet_url' => $gs->url($spreadsheetId),
                'last_exported_registration_id' => 0,
            ]);
            $statusColumnIndex = 5;
            $gs->formatPaidGreen($spreadsheetId, $sheetId, $statusColumnIndex);
        } else {
            $gs->writeHeader($export->spreadsheet_id, $header);
        
            // apply juga untuk existing sheet (aman walau berulang)
            $sheetId = $gs->getFirstSheetId($export->spreadsheet_id);
            $gs->formatPaidGreen($export->spreadsheet_id, $sheetId, 5);
        }


        // last exported id (untuk append incremental)
        $lastId = (int) $export->last_exported_registration_id;

        // Map existing rows in sheet: registration_id => rowNumber
        $idRowMap = $gs->getIdRowMap($export->spreadsheet_id);

        // Helper build row data
        $buildRow = function (EventRegistration $r) use ($fields) {
            $answers = $r->answers->pluck('value', 'field_key')->toArray();

            $base = [
                $r->id,
                optional($r->created_at)->format('Y-m-d H:i:s'),
                $r->name,
                $r->email,
                $r->phone,
                $r->status,
                $r->price_name_snapshot,
                $r->amount_snapshot,
            ];

            $dynamic = $fields->map(function ($f) use ($answers) {
                $val = $answers[$f->field_key] ?? '';
                if (is_array($val)) return implode(', ', array_map('strval', $val));
                return (string) $val;
            })->toArray();

            return array_merge($base, $dynamic);
        };

        /**
         * ✅ 1) UPDATE existing rows (yang sudah pernah di-export)
         * Cara simple: update semua id <= lastId yang ada di sheet.
         * Jadi kalau status berubah (registered→paid), row-nya di-update saat export berikutnya.
         */
        if ($lastId > 0 && !empty($idRowMap)) {
            $existingRegs = EventRegistration::where('event_id', $event->id)
                ->where('id', '<=', $lastId)
                ->with('answers')
                ->orderBy('id')
                ->get();

            foreach ($existingRegs as $r) {
                if (isset($idRowMap[$r->id])) {
                    $gs->updateRow($export->spreadsheet_id, $idRowMap[$r->id], $buildRow($r));
                }
            }
        }

        /**
         * ✅ 2) APPEND new rows (id > lastId)
         */
        $newRegs = EventRegistration::where('event_id', $event->id)
            ->where('id', '>', $lastId)
            ->with('answers')
            ->orderBy('id')
            ->get();

        $appendRows = [];
        foreach ($newRegs as $r) {
            // kalau ternyata sudah ada di sheet (misalnya sheet pernah di-edit), update saja
            if (isset($idRowMap[$r->id])) {
                $gs->updateRow($export->spreadsheet_id, $idRowMap[$r->id], $buildRow($r));
            } else {
                $appendRows[] = $buildRow($r);
            }
        }

        $gs->appendRows($export->spreadsheet_id, $appendRows);

        if ($newRegs->isNotEmpty()) {
            $export->update(['last_exported_registration_id' => $newRegs->max('id')]);
        }

        return redirect()->away($export->spreadsheet_url);
    }
}
