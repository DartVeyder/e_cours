<?php

namespace App\Services\GoogleSheet;

use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\SpreadsheetProperties;
use Google\Service\Sheets\ValueRange;

class GoogleSheetModel
{
    protected Sheets $service;
    protected string $spreadsheetId;
    protected array $headersMap;
    protected string $range;

    public function __construct(string $range)
    {
        $this->spreadsheetId = $this->getSpreadsheetId();
        $this->headersMap = $this->getHeadersMap();
        $this->range = $range;
        $this->service = app(GoogleSheetService::class)->getService();
    }

    public function createSheet(string $title, int $rows = 100, int $cols = 10): int
    {
        // Отримуємо всю інформацію про таблицю
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);

        // Перевіряємо чи є вже лист з такою назвою
        foreach ($spreadsheet->getSheets() as $sheet) {
            $properties = $sheet->getProperties();
            if ($properties->getTitle() === $title) {
                return $properties->getSheetId(); // якщо існує — повертаємо id
            }
        }

        // Якщо нема — створюємо новий
        $addSheetRequest = new \Google\Service\Sheets\AddSheetRequest([
            'properties' => [
                'title' => $title,
            ]
        ]);

        $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [
                ['addSheet' => $addSheetRequest]
            ]
        ]);

        $response = $this->service->spreadsheets->batchUpdate(
            $this->spreadsheetId,
            $batchUpdateRequest
        );

        // sheetId нового листа
        return $response->getReplies()[0]->getAddSheet()->getProperties()->getSheetId();
    }

    public function getSheetTitleById(int $sheetId): ?string
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getSheetId() === $sheetId) {
                return $sheet->getProperties()->getTitle();
            }
        }
        return null;
    }

    public function writeBySheetId(int $sheetId, array $values, string $startCell = 'A1'): mixed
    {
        $title = $this->getSheetTitleById($sheetId);
        if (!$title) {
            throw new \Exception("Лист із ID {$sheetId} не знайдено.");
        }

        $range = $title ;

        // 🔹 Очистка всіх попередніх даних
        $this->service->spreadsheets_values->clear(
            $this->spreadsheetId,
            $range,
            new \Google\Service\Sheets\ClearValuesRequest()
        );

        // 🔹 Запис нових
        $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];
        sleep(1);
        return $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );
    }

    protected function getSpreadsheetId(): string
    {
        return ''; // це буде замінено в дочірньому класі
    }

    protected function getHeadersMap(): array
    {
        return [];
    }

    public function readSheet(): array
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->range);
        return $response->getValues() ?? [];
    }

    public function writeSheet(array $values): mixed
    {
        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];
        return $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $this->range,
            $body,
            $params
        );
    }

    public function createSpreadsheet(string $title): string
    {
        $service = app(GoogleSheetService::class)->getService();

        $spreadsheet = new Spreadsheet([
            'properties' => new SpreadsheetProperties([
                'title' => $title,
            ]),
        ]);

        $response = $service->spreadsheets->create($spreadsheet);

        // Отримуємо ID нової таблиці
        return $response->spreadsheetId;
    }

    private function changeHeaders(array $headersMap, $headers): array
    {
        $data = [];
         foreach ($headers as $header) {
             $data[] = $headersMap[$header] ?? $header;
         }
        return  $data;
    }

    public function readAssoc(): array
    {
        $headersMap = $this->headersMap;
        $rows = $this->readSheet();


        if (count($rows) < 2) return [];

        $headers = array_shift($rows);

        $headers = $this->changeHeaders($headersMap, $headers);

        $data = [];

        foreach ($rows as $row){
            foreach ($row as &$cell) {
                if($cell == ''){
                    $cell = null;
                }
            }
            $data[] = array_combine($headers, $row + array_fill(0, count($headers), null));
        }
        return $data;
    }
}
