<?php


namespace App\Services\GoogleSheet;


class GoogleSheetService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new \Google\Client();
        $this->client->setAuthConfig(storage_path('app/google/ecoursauth-69f5c8e85788.json'));
        $this->client->setScopes([\Google_Service_Sheets::SPREADSHEETS, \Google_Service_Drive::DRIVE]);
        $this->client->setAccessType('offline');
        // ініціалізація Google Sheets API
        $this->service = new \Google\Service\Sheets($this->client);
    }

    // Метод для отримання доступу до об'єкта служби Google Sheets
    public function getService()
    {
        return $this->service;
    }

    public const DEFAULT_STUDENTS_SHEET_ID = '1mgLhc_jg_XSFbXjqx32xLzXTapHNMyR1kF9xASkHh_A';
    public const DEFAULT_GROUPS_SHEET_ID   = '1mgLhc_jg_XSFbXjqx32xLzXTapHNMyR1kF9xASkHh_A';
    public const DEFAULT_SUBJECTS_SHEET_ID = '1DeCO1hKHqcYPcriPcaIz3LAZVCFKpmfjdkspNu1Is2w';

    /**
     * Отримати ID активної таблиці (робочої або тестової).
     */
    public static function getSheetId(string $key): string
    {
        $isTestMode = self::isTestMode();
        $mode = $isTestMode ? 'test' : 'production';

        // 1. Отримання з БД (таблиця settings)
        $dbKey = "google_sheets_{$mode}_{$key}_id";
        $dbValue = \App\Models\Setting::where('key', $dbKey)->value('value');
        if (!empty($dbValue)) {
            return trim($dbValue);
        }

        // 2. Якщо в тестовому режимі не вказано свій ID — беремо робочий ID
        if ($isTestMode) {
            $prodDbValue = \App\Models\Setting::where('key', "google_sheets_production_{$key}_id")->value('value');
            if (!empty($prodDbValue)) {
                return trim($prodDbValue);
            }
        }

        // 3. Резервні константи за замовчуванням
        return match ($key) {
            'students' => self::DEFAULT_STUDENTS_SHEET_ID,
            'groups'   => self::DEFAULT_GROUPS_SHEET_ID,
            'subjects' => self::DEFAULT_SUBJECTS_SHEET_ID,
            default    => '',
        };
    }

    /**
     * Отримати URL активної таблиці.
     */
    public static function getSheetUrl(string $key): string
    {
        $id = self::getSheetId($key);
        return "https://docs.google.com/spreadsheets/d/{$id}/edit?usp=sharing";
    }

    /**
     * Отримати назву аркуша (вкладки) таблиці.
     */
    public static function getSheetTab(string $key, string $default = ''): string
    {
        $isTestMode = self::isTestMode();
        $mode = $isTestMode ? 'test' : 'production';

        // 1. Пошук для активного режиму (production / test)
        $tabMode = \App\Models\Setting::where('key', "google_sheets_{$mode}_{$key}_tab")->value('value');
        if (!empty($tabMode)) {
            return trim($tabMode);
        }

        // 2. Якщо в тестовому режимі назву аркуша не вказано — беремо робочий аркуш
        if ($isTestMode) {
            $prodTab = \App\Models\Setting::where('key', "google_sheets_production_{$key}_tab")->value('value');
            if (!empty($prodTab)) {
                return trim($prodTab);
            }
        }

        // 3. Загальне налаштування (для зворотної сумісності)
        $tabGeneral = \App\Models\Setting::where('key', "google_sheets_{$key}_tab")->value('value');
        if (!empty($tabGeneral)) {
            return trim($tabGeneral);
        }

        return $default;
    }

    /**
     * Перевірити, чи увімкнено режим тестових таблиць.
     */
    public static function isTestMode(): bool
    {
        $dbSetting = \App\Models\Setting::where('key', 'google_sheets_test_mode')->value('value');
        if ($dbSetting !== null) {
            return $dbSetting === '1';
        }

        return false;
    }
}

