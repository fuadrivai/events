<?php

namespace App\Services;

use App\Models\GoogleOwnerToken;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\Permission;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\ConditionalFormatRule;
use Google\Service\Sheets\BooleanRule;
use Google\Service\Sheets\ConditionValue;
use Google\Service\Sheets\GridRange;
use Google\Service\Sheets\CellFormat;
use Google\Service\Sheets\Color;
use Google\Service\Sheets\TextFormat;
class GoogleSheetsExporter
{
    private function ownerToken(): GoogleOwnerToken
    {
        $row = GoogleOwnerToken::where('owner_email', config('google.owner_email'))->first();
        if (!$row || !$row->refresh_token) {
            throw new \RuntimeException('Google Owner belum connect. Buka /google/connect dan login pakai akun owner.');
        }
        return $row;
    }

    private function client(): Client
    {
        $row = $this->ownerToken();

        $client = new Client();
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(config('google.redirect_uri'));

        $client->setScopes([
            Sheets::SPREADSHEETS,
            Drive::DRIVE,
        ]);

        // Pakai refresh token untuk generate access token baru bila perlu
        $client->refreshToken($row->refresh_token);
        $token = $client->getAccessToken();

        // simpan access token terbaru (optional tapi bagus)
        $row->update([
            'access_token' => json_encode($token),
            'expires_at' => now()->addSeconds((int)($token['expires_in'] ?? 3600)),
        ]);

        return $client;
    }

    private function sheets(): Sheets
    {
        return new Sheets($this->client());
    }

    private function drive(): Drive
    {
        return new Drive($this->client());
    }

    public function createSpreadsheet(string $title): string
    {
        $spreadsheet = new Spreadsheet(['properties' => ['title' => $title]]);
        return $this->sheets()->spreadsheets->create($spreadsheet)->spreadsheetId;
    }

    public function writeHeader(string $spreadsheetId, array $headers): void
    {
        $this->sheets()->spreadsheets_values->update(
            $spreadsheetId,
            'Sheet1!A1',
            new ValueRange(['values' => [$headers]]),
            ['valueInputOption' => 'RAW']
        );
    }

    public function appendRows(string $spreadsheetId, array $rows): void
    {
        if (empty($rows)) return;

        $this->sheets()->spreadsheets_values->append(
            $spreadsheetId,
            'Sheet1!A2',
            new ValueRange(['values' => $rows]),
            ['valueInputOption' => 'RAW', 'insertDataOption' => 'INSERT_ROWS']
        );
    }

    public function shareToEmail(string $spreadsheetId, string $email, string $role = 'writer'): void
    {
        $perm = new Permission([
            'type' => 'user',
            'role' => $role,
            'emailAddress' => $email,
        ]);

        // NOTE: kalau workspace blok external sharing, ini akan error (policy)
        $this->drive()->permissions->create(
            $spreadsheetId,
            $perm,
            ['sendNotificationEmail' => false]
        );
    }

    public function url(string $spreadsheetId): string
    {
        return "https://docs.google.com/spreadsheets/d/{$spreadsheetId}";
    }
    public function getIdRowMap(string $spreadsheetId): array
    {
        $resp = $this->sheets()->spreadsheets_values->get(
            $spreadsheetId,
            'Sheet1!A2:A' // kolom ID
        );
    
        $map = [];
        $rows = $resp->getValues() ?? [];
    
        foreach ($rows as $i => $row) {
            if (!empty($row[0])) {
                $map[(int)$row[0]] = $i + 2; // +2 karena mulai dari row 2
            }
        }
    
        return $map;
    }
    public function updateRow(string $spreadsheetId, int $rowNumber, array $rowData): void
    {
        $range = "Sheet1!A{$rowNumber}";
        $this->sheets()->spreadsheets_values->update(
            $spreadsheetId,
            $range,
            new \Google\Service\Sheets\ValueRange([
                'values' => [$rowData],
            ]),
            ['valueInputOption' => 'RAW']
        );
    }
    public function formatPaidGreen(string $spreadsheetId, int $sheetId, int $statusColumnIndex): void
    {
        // convert index -> column letter (0=A, 5=F, dst)
        $colLetter = function (int $index): string {
            $index += 1;
            $letters = '';
            while ($index > 0) {
                $mod = ($index - 1) % 26;
                $letters = chr(65 + $mod) . $letters;
                $index = intdiv($index - 1, 26);
            }
            return $letters;
        };
    
        $col = $colLetter($statusColumnIndex);
        // row data dimulai dari 2, jadi refer ke F2 (kalau status col = 5)
        $formula = "=LOWER(TRIM({$col}2))=\"paid\"";
    
        $rule = new ConditionalFormatRule([
            'ranges' => [
                new GridRange([
                    'sheetId' => $sheetId,
                    'startRowIndex' => 1, // skip header
                    'startColumnIndex' => $statusColumnIndex,
                    'endColumnIndex' => $statusColumnIndex + 1,
                ]),
            ],
            'booleanRule' => new BooleanRule([
                'condition' => [
                    'type' => 'CUSTOM_FORMULA',
                    'values' => [
                        new ConditionValue(['userEnteredValue' => $formula]),
                    ],
                ],
                'format' => new CellFormat([
                    'backgroundColor' => new Color(['red' => 0.2, 'green' => 0.7, 'blue' => 0.2]),
                    'textFormat' => new TextFormat([
                        'foregroundColor' => new Color(['red' => 1, 'green' => 1, 'blue' => 1]),
                        'bold' => true,
                    ]),
                ]),
            ]),
        ]);
    
        $this->sheets()->spreadsheets->batchUpdate(
            $spreadsheetId,
            new BatchUpdateSpreadsheetRequest([
                'requests' => [[
                    'addConditionalFormatRule' => [
                        'rule' => $rule,
                        'index' => 0,
                    ],
                ]],
            ])
        );
    }

    public function getFirstSheetId(string $spreadsheetId): int
    {
        $spreadsheet = $this->sheets()->spreadsheets->get($spreadsheetId);
        return $spreadsheet->getSheets()[0]->getProperties()->getSheetId();
    }
    private function colLetter(int $index): string
{
    $index += 1; // 0-based ke 1-based
    $letters = '';
    while ($index > 0) {
        $mod = ($index - 1) % 26;
        $letters = chr(65 + $mod) . $letters;
        $index = intdiv($index - 1, 26);
    }
    return $letters;
}

public function applyPaidGreenRule(string $spreadsheetId, int $sheetId, int $statusColumnIndex): void
{
    $col = $this->colLetter($statusColumnIndex); // mis. 5 -> F
    // data mulai row 2, jadi pakai F2 sebagai referensi
    $formula = "=LOWER(TRIM({$col}2))=\"paid\"";

    $rule = new ConditionalFormatRule([
        'ranges' => [
            new GridRange([
                'sheetId' => $sheetId,
                'startRowIndex' => 1, // skip header
                'startColumnIndex' => $statusColumnIndex,
                'endColumnIndex' => $statusColumnIndex + 1,
            ]),
        ],
        'booleanRule' => new BooleanRule([
            'condition' => [
                'type' => 'CUSTOM_FORMULA',
                'values' => [
                    new ConditionValue(['userEnteredValue' => $formula]),
                ],
            ],
            'format' => new CellFormat([
                'backgroundColor' => new Color(['red' => 0.20, 'green' => 0.70, 'blue' => 0.20]),
                'textFormat' => new TextFormat([
                    'foregroundColor' => new Color(['red' => 1, 'green' => 1, 'blue' => 1]),
                    'bold' => true,
                ]),
            ]),
        ]),
    ]);

    $this->sheets()->spreadsheets->batchUpdate(
        $spreadsheetId,
        new BatchUpdateSpreadsheetRequest([
            'requests' => [[
                'addConditionalFormatRule' => [
                    'rule' => $rule,
                    'index' => 0,
                ],
            ]],
        ])
    );
}

}
