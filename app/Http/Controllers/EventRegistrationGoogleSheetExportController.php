<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GoogleSheetExport;
use App\Models\Payment;
use App\Services\GoogleSheetsExporter;
use Illuminate\Http\Request;

class EventRegistrationGoogleSheetExportController extends Controller
{
    public function export(
        Request $request,
        string $eventId,
        GoogleSheetsExporter $gs
    ) {
        $user = $request->user();

        abort_unless(
            $user && $user->email,
            403,
            'User email not found.'
        );

        $event = Event::where(
            'id',
            $eventId
        )->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Event fields
        |--------------------------------------------------------------------------
        */

        $fields = $event->fields()
            ->orderBy('order_index')
            ->get([
                'field_key',
                'label',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $header = array_merge(
            [
                'ID',
                'Created At',
                'Name',
                'Email',
                'Phone',
                'Registration Code',
                'External ID',
                'Status',
                'Package',
                'Amount',
            ],
            $fields
                ->map(
                    fn ($f) =>
                        $f->label ?: $f->field_key
                )
                ->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Get export record
        |--------------------------------------------------------------------------
        */

        $export = GoogleSheetExport::where(
            'user_id',
            $user->id
        )
            ->where(
                'event_id',
                $event->id
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | CREATE NEW SPREADSHEET
        |--------------------------------------------------------------------------
        */

        if (!$export) {

            $spreadsheetId = $gs->createSpreadsheet(
                'Registrations - '
                . $event->title
                . ' - '
                . $user->email
            );

            $sheetId = $gs->getFirstSheetId(
                $spreadsheetId
            );

            /*
            |--------------------------------------------------------------------------
            | Write header hanya saat spreadsheet dibuat
            |--------------------------------------------------------------------------
            */

            $gs->writeHeader(
                $spreadsheetId,
                $header
            );

            /*
            |--------------------------------------------------------------------------
            | Share spreadsheet ke user
            |--------------------------------------------------------------------------
            */

            $gs->shareToEmail(
                $spreadsheetId,
                $user->email,
                'writer'
            );

            /*
            |--------------------------------------------------------------------------
            | Create export record
            |--------------------------------------------------------------------------
            */

            $export = GoogleSheetExport::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'spreadsheet_id' => $spreadsheetId,
                'spreadsheet_url' => $gs->url(
                    $spreadsheetId
                ),
                'last_exported_registration_id' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Status berada di kolom F
            |--------------------------------------------------------------------------
            |
            | A = 0
            | B = 1
            | C = 2
            | D = 3
            | E = 4
            | F = 5
            |
            */

            $gs->formatPaidGreen(
                $spreadsheetId,
                $sheetId,
                5
            );

        } else {

            /*
            |--------------------------------------------------------------------------
            | Existing spreadsheet
            |--------------------------------------------------------------------------
            |
            | JANGAN:
            |
            | - writeHeader()
            | | formatPaidGreen()
            |
            | Karena keduanya adalah write request.
            |--------------------------------------------------------------------------
            */

            $spreadsheetId = $export->spreadsheet_id;

            $sheetId = $gs->getFirstSheetId(
                $spreadsheetId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Last exported registration ID
        |--------------------------------------------------------------------------
        */

        $lastId = (int) (
            $export->last_exported_registration_id
            ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | Get existing rows from Google Sheet
        |--------------------------------------------------------------------------
        |
        | Result:
        |
        | [
        |     100 => [
        |         'row' => 2,
        |         'status' => 'INVOICED',
        |     ],
        |     101 => [
        |         'row' => 3,
        |         'status' => 'PAID',
        |     ],
        | ]
        |
        */

        $idRowMap = $gs->getIdRowMap(
            $spreadsheetId
        );

        /*
        |--------------------------------------------------------------------------
        | Helper build row
        |--------------------------------------------------------------------------
        */

        $buildRow = function (
            EventRegistration $r
        ) use ($fields) {

            $answers = $r->answers
                ->pluck(
                    'value',
                    'field_key'
                )
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | Base columns
            |--------------------------------------------------------------------------
            */

            $externalId = Payment::where('registration_id', $r->id)
                ->value('external_id');

            $base = [
                $r->id,

                optional($r->created_at)
                    ->format('Y-m-d H:i:s'),

                $r->name,
                $r->email,
                $r->phone,
                $r->registration_code,
                $externalId ?? '',
                $r->status,
                $r->price_name_snapshot,
                $r->amount_snapshot,
            ];

            /*
            |--------------------------------------------------------------------------
            | Dynamic fields
            |--------------------------------------------------------------------------
            */

            $dynamic = $fields->map(
                function ($f) use ($answers) {

                    $val = $answers[
                        $f->field_key
                    ] ?? '';

                    if (is_array($val)) {
                        return implode(
                            ', ',
                            array_map(
                                'strval',
                                $val
                            )
                        );
                    }

                    return (string) $val;
                }
            )->toArray();

            return array_merge(
                $base,
                $dynamic
            );
        };

        /*
        |--------------------------------------------------------------------------
        | UPDATE EXISTING DATA
        |--------------------------------------------------------------------------
        |
        | Hanya update jika status database berbeda
        | dengan status yang ada di Google Sheet.
        |--------------------------------------------------------------------------
        */

        $updates = [];

        if (
            $lastId > 0 &&
            !empty($idRowMap)
        ) {

            $existingRegs = EventRegistration::where(
                'event_id',
                $event->id
            )
                ->where(
                    'id',
                    '<=',
                    $lastId
                )
                ->with('answers')
                ->orderBy('id')
                ->get();

            foreach ($existingRegs as $r) {

                /*
                |--------------------------------------------------------------------------
                | ID tidak ditemukan di Google Sheet
                |--------------------------------------------------------------------------
                */

                if (!isset($idRowMap[$r->id])) {
                    continue;
                }

                $sheetRow = $idRowMap[$r->id];

                /*
                |--------------------------------------------------------------------------
                | Status database
                |--------------------------------------------------------------------------
                */

                $dbStatus = strtoupper(
                    trim(
                        (string) $r->status
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | Status Google Sheet
                |--------------------------------------------------------------------------
                */

                $sheetStatus = strtoupper(
                    trim(
                        (string) $sheetRow['status']
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | Hanya update jika status berubah
                |--------------------------------------------------------------------------
                */

                if ($dbStatus !== $sheetStatus) {

                    $updates[] = [
                        'row' => $sheetRow['row'],
                        'data' => $buildRow($r),
                    ];
                }
            }
        }
        $newRegs = EventRegistration::where(
            'event_id',
            $event->id
        )
            ->where(
                'id',
                '>',
                $lastId
            )
            ->with('answers')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Rows untuk append
        |--------------------------------------------------------------------------
        */

        $appendRows = [];

        foreach ($newRegs as $r) {

            /*
            |--------------------------------------------------------------------------
            | Safety check:
            | kalau ID ternyata sudah ada di Google Sheet
            |--------------------------------------------------------------------------
            */

            if (isset($idRowMap[$r->id])) {

                $sheetRow = $idRowMap[$r->id];

                $dbStatus = strtoupper(
                    trim(
                        (string) $r->status
                    )
                );

                $sheetStatus = strtoupper(
                    trim(
                        (string) $sheetRow['status']
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | Update hanya jika status berbeda
                |--------------------------------------------------------------------------
                */

                if ($dbStatus !== $sheetStatus) {

                    $updates[] = [
                        'row' => $sheetRow['row'],
                        'data' => $buildRow($r),
                    ];
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Benar-benar data baru
            |--------------------------------------------------------------------------
            */

            $appendRows[] = $buildRow($r);
        }

        /*
        |--------------------------------------------------------------------------
        | BATCH UPDATE EXISTING ROWS
        |--------------------------------------------------------------------------
        |
        | Maksimal 200 row per request.
        |
        | Misalnya:
        |
        | 1.000 status berubah
        |
        | 200 + 200 + 200 + 200 + 200
        |
        | = 5 API write request
        |--------------------------------------------------------------------------
        */

        if (!empty($updates)) {

            foreach (
                array_chunk($updates, 200)
                as $chunk
            ) {

                $gs->updateRows(
                    $spreadsheetId,
                    $chunk
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | APPEND NEW ROWS
        |--------------------------------------------------------------------------
        |
        | Semua data baru dikirim sekaligus.
        |--------------------------------------------------------------------------
        */

        if (!empty($appendRows)) {

            $gs->appendRows(
                $spreadsheetId,
                $appendRows
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Update last exported registration ID
        |--------------------------------------------------------------------------
        */

        if ($newRegs->isNotEmpty()) {

            $export->update([
                'last_exported_registration_id'
                    => $newRegs->max('id'),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect ke Google Sheet
        |--------------------------------------------------------------------------
        */

        return redirect()->away(
            $export->spreadsheet_url
        );
    }
}
