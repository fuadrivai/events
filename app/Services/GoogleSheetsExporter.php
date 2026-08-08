<?php

namespace App\Services;

use App\Models\GoogleOwnerToken;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\Permission;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\BooleanRule;
use Google\Service\Sheets\CellFormat;
use Google\Service\Sheets\ConditionValue;
use Google\Service\Sheets\ConditionalFormatRule;
use Google\Service\Sheets\GridRange;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;

class GoogleSheetsExporter
{
    private function ownerToken(): GoogleOwnerToken
    {
        $row = GoogleOwnerToken::where(
            'owner_email',
            config('google.owner_email')
        )->first();

        if (!$row || !$row->refresh_token) {
            throw new \RuntimeException(
                'Google Owner belum connect. Buka /google/connect dan login pakai akun owner.'
            );
        }

        return $row;
    }

    private function client(): Client
    {
        $row = $this->ownerToken();

        $client = new Client();

        $client->setClientId(
            config('google.client_id')
        );

        $client->setClientSecret(
            config('google.client_secret')
        );

        $client->setRedirectUri(
            config('google.redirect_uri')
        );

        $client->setScopes([
            Sheets::SPREADSHEETS,
            Drive::DRIVE,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate access token menggunakan refresh token
        |--------------------------------------------------------------------------
        */

        $client->refreshToken(
            $row->refresh_token
        );

        $token = $client->getAccessToken();

        /*
        |--------------------------------------------------------------------------
        | Simpan access token terbaru
        |--------------------------------------------------------------------------
        */

        $row->update([
            'access_token' => json_encode($token),
            'expires_at' => now()->addSeconds(
                (int) ($token['expires_in'] ?? 3600)
            ),
        ]);

        return $client;
    }

    private function sheets(): Sheets
    {
        return new Sheets(
            $this->client()
        );
    }

    private function drive(): Drive
    {
        return new Drive(
            $this->client()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE SPREADSHEET
    |--------------------------------------------------------------------------
    */

    public function createSpreadsheet(
        string $title
    ): string {
        $spreadsheet = new Spreadsheet([
            'properties' => [
                'title' => $title,
            ],
        ]);

        return $this->sheets()
            ->spreadsheets
            ->create($spreadsheet)
            ->spreadsheetId;
    }

    /*
    |--------------------------------------------------------------------------
    | WRITE HEADER
    |--------------------------------------------------------------------------
    */

    public function writeHeader(
        string $spreadsheetId,
        array $headers
    ): void {
        $this->sheets()
            ->spreadsheets_values
            ->update(
                $spreadsheetId,
                'Sheet1!A1',
                new ValueRange([
                    'values' => [
                        $headers,
                    ],
                ]),
                [
                    'valueInputOption' => 'RAW',
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | APPEND ROWS
    |--------------------------------------------------------------------------
    */

    public function appendRows(
        string $spreadsheetId,
        array $rows
    ): void {
        if (empty($rows)) {
            return;
        }

        $this->sheets()
            ->spreadsheets_values
            ->append(
                $spreadsheetId,
                'Sheet1!A2',
                new ValueRange([
                    'values' => $rows,
                ]),
                [
                    'valueInputOption' => 'RAW',
                    'insertDataOption' => 'INSERT_ROWS',
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHARE TO EMAIL
    |--------------------------------------------------------------------------
    */

    public function shareToEmail(
        string $spreadsheetId,
        string $email,
        string $role = 'writer'
    ): void {
        $permission = new Permission([
            'type' => 'user',
            'role' => $role,
            'emailAddress' => $email,
        ]);

        $this->drive()
            ->permissions
            ->create(
                $spreadsheetId,
                $permission,
                [
                    'sendNotificationEmail' => false,
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SPREADSHEET URL
    |--------------------------------------------------------------------------
    */

    public function url(
        string $spreadsheetId
    ): string {
        return "https://docs.google.com/spreadsheets/d/{$spreadsheetId}";
    }

    /*
    |--------------------------------------------------------------------------
    | GET ID -> ROW + STATUS MAP
    |--------------------------------------------------------------------------
    |
    | Mengambil:
    |
    | A = ID
    | F = Status
    |
    | Result:
    |
    | [
    |     123 => [
    |         'row' => 2,
    |         'status' => 'PAID',
    |     ],
    |     124 => [
    |         'row' => 3,
    |         'status' => 'INVOICED',
    |     ],
    | ]
    |
    */

    public function getIdRowMap(
        string $spreadsheetId
    ): array {
        $response = $this->sheets()
            ->spreadsheets_values
            ->get(
                $spreadsheetId,
                'Sheet1!A:F'
            );

        $rows = $response->getValues() ?? [];

        $map = [];

        foreach ($rows as $index => $row) {

            /*
            |--------------------------------------------------------------------------
            | Skip header
            |--------------------------------------------------------------------------
            */

            if ($index === 0) {
                continue;
            }

            $id = $row[0] ?? null;

            if (!$id || !is_numeric($id)) {
                continue;
            }

            $map[(int) $id] = [
                'row' => $index + 1,

                /*
                |--------------------------------------------------------------------------
                | F = Status
                |--------------------------------------------------------------------------
                */

                'status' => strtoupper(
                    trim(
                        (string) ($row[5] ?? '')
                    )
                ),
            ];
        }

        return $map;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE SINGLE ROW
    |--------------------------------------------------------------------------
    |
    | Tetap dipertahankan jika masih digunakan di tempat lain.
    |
    */

    public function updateRow(
        string $spreadsheetId,
        int $rowNumber,
        array $rowData
    ): void {
        $range = "Sheet1!A{$rowNumber}";

        $this->sheets()
            ->spreadsheets_values
            ->update(
                $spreadsheetId,
                $range,
                new ValueRange([
                    'values' => [
                        $rowData,
                    ],
                ]),
                [
                    'valueInputOption' => 'RAW',
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE MANY ROWS
    |--------------------------------------------------------------------------
    |
    | Menggunakan spreadsheets.values.batchUpdate().
    |
    | Contoh:
    |
    | 200 row berubah
    |        ↓
    | 1 API write request
    |
    */

    public function updateRows(
        string $spreadsheetId,
        array $updates
    ): void {
        if (empty($updates)) {
            return;
        }

        $data = [];

        foreach ($updates as $update) {

            $rowNumber = (int) $update['row'];

            $rowData = $update['data'];

            $data[] = new ValueRange([
                'range' => "Sheet1!A{$rowNumber}",
                'values' => [
                    $rowData,
                ],
            ]);
        }

        $request = new BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data,
        ]);

        $this->sheets()
            ->spreadsheets_values
            ->batchUpdate(
                $spreadsheetId,
                $request
            );
    }

    /*
    |--------------------------------------------------------------------------
    | GET FIRST SHEET ID
    |--------------------------------------------------------------------------
    */

    public function getFirstSheetId(
        string $spreadsheetId
    ): int {
        $spreadsheet = $this->sheets()
            ->spreadsheets
            ->get($spreadsheetId);

        return $spreadsheet
            ->getSheets()[0]
            ->getProperties()
            ->getSheetId();
    }

    /*
    |--------------------------------------------------------------------------
    | COLUMN LETTER
    |--------------------------------------------------------------------------
    |
    | 0 = A
    | 1 = B
    | 5 = F
    |
    */

    private function colLetter(
        int $index
    ): string {
        $index += 1;

        $letters = '';

        while ($index > 0) {

            $mod = ($index - 1) % 26;

            $letters = chr(
                65 + $mod
            ) . $letters;

            $index = intdiv(
                $index - 1,
                26
            );
        }

        return $letters;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT PAID GREEN
    |--------------------------------------------------------------------------
    */

    public function formatPaidGreen(
        string $spreadsheetId,
        int $sheetId,
        int $statusColumnIndex
    ): void {
        $col = $this->colLetter(
            $statusColumnIndex
        );

        /*
        |--------------------------------------------------------------------------
        | Status column:
        | F2
        |--------------------------------------------------------------------------
        */

        $formula = '=LOWER(TRIM(' . $col . '2))="paid"';

        $rule = new ConditionalFormatRule([
            'ranges' => [
                new GridRange([
                    'sheetId' => $sheetId,

                    /*
                    |--------------------------------------------------------------------------
                    | Skip header
                    |--------------------------------------------------------------------------
                    */

                    'startRowIndex' => 1,

                    'startColumnIndex' => $statusColumnIndex,

                    'endColumnIndex' => $statusColumnIndex + 1,
                ]),
            ],

            'booleanRule' => new BooleanRule([
                'condition' => [
                    'type' => 'CUSTOM_FORMULA',

                    'values' => [
                        new ConditionValue([
                            'userEnteredValue' => $formula,
                        ]),
                    ],
                ],

                'format' => new CellFormat([
                    'backgroundColor' => [
                        'red' => 0.20,
                        'green' => 0.70,
                        'blue' => 0.20,
                    ],

                    'textFormat' => [
                        'foregroundColor' => [
                            'red' => 1,
                            'green' => 1,
                            'blue' => 1,
                        ],
                        'bold' => true,
                    ],
                ]),
            ]),
        ]);

        $this->sheets()
            ->spreadsheets
            ->batchUpdate(
                $spreadsheetId,
                new BatchUpdateSpreadsheetRequest([
                    'requests' => [
                        [
                            'addConditionalFormatRule' => [
                                'rule' => $rule,
                                'index' => 0,
                            ],
                        ],
                    ],
                ])
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Alias jika sebelumnya menggunakan applyPaidGreenRule()
    |--------------------------------------------------------------------------
    */

    public function applyPaidGreenRule(
        string $spreadsheetId,
        int $sheetId,
        int $statusColumnIndex
    ): void {
        $this->formatPaidGreen(
            $spreadsheetId,
            $sheetId,
            $statusColumnIndex
        );
    }
}
