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
        $this->client->addScope(\Google\Service\Sheets::SPREADSHEETS);

        // ініціалізація Google Sheets API
        $this->service = new \Google\Service\Sheets($this->client);
    }

    // Метод для отримання доступу до об'єкта служби Google Sheets
    public function getService()
    {
        return $this->service;
    }
}
