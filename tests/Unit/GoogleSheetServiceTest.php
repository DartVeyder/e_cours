<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\GoogleSheet\GoogleSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleSheetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_test_mode_returns_false_by_default(): void
    {
        $this->assertFalse(GoogleSheetService::isTestMode());
    }

    public function test_is_test_mode_reads_from_database_setting(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_test_mode'], ['value' => '1']);
        $this->assertTrue(GoogleSheetService::isTestMode());

        Setting::updateOrCreate(['key' => 'google_sheets_test_mode'], ['value' => '0']);
        $this->assertFalse(GoogleSheetService::isTestMode());
    }

    public function test_get_sheet_id_returns_production_defaults_when_empty(): void
    {
        $studentsId = GoogleSheetService::getSheetId('students');
        $this->assertEquals(GoogleSheetService::DEFAULT_STUDENTS_SHEET_ID, $studentsId);

        $subjectsId = GoogleSheetService::getSheetId('subjects');
        $this->assertEquals(GoogleSheetService::DEFAULT_SUBJECTS_SHEET_ID, $subjectsId);

        $groupsId = GoogleSheetService::getSheetId('groups');
        $this->assertEquals(GoogleSheetService::DEFAULT_GROUPS_SHEET_ID, $groupsId);
    }

    public function test_get_sheet_id_returns_custom_production_id_from_database(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_production_students_id'], ['value' => 'custom-prod-students-id']);

        $id = GoogleSheetService::getSheetId('students');
        $this->assertEquals('custom-prod-students-id', $id);
    }

    public function test_get_sheet_id_returns_test_id_when_test_mode_is_enabled(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_test_mode'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'google_sheets_test_students_id'], ['value' => 'custom-test-students-id']);

        $id = GoogleSheetService::getSheetId('students');
        $this->assertEquals('custom-test-students-id', $id);
    }

    public function test_get_sheet_id_falls_back_to_production_id_in_test_mode_if_test_id_empty(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_test_mode'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'google_sheets_production_students_id'], ['value' => 'my-main-prod-id']);
        Setting::updateOrCreate(['key' => 'google_sheets_test_students_id'], ['value' => '']);

        $id = GoogleSheetService::getSheetId('students');
        $this->assertEquals('my-main-prod-id', $id);
    }

    public function test_get_sheet_tab_returns_default_tab_when_no_setting(): void
    {
        $tab = GoogleSheetService::getSheetTab('students', 'Студенти');
        $this->assertEquals('Студенти', $tab);
    }

    public function test_get_sheet_tab_returns_custom_production_tab(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_production_students_tab'], ['value' => 'Картки_2026']);

        $tab = GoogleSheetService::getSheetTab('students', 'Студенти');
        $this->assertEquals('Картки_2026', $tab);
    }

    public function test_get_sheet_tab_returns_custom_test_tab_in_test_mode(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_test_mode'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'google_sheets_test_students_tab'], ['value' => 'Студенти_Тест']);

        $tab = GoogleSheetService::getSheetTab('students', 'Студенти');
        $this->assertEquals('Студенти_Тест', $tab);
    }

    public function test_get_sheet_tab_falls_back_to_production_tab_in_test_mode_if_test_tab_empty(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_test_mode'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'google_sheets_production_students_tab'], ['value' => 'Головний_Аркуш']);
        Setting::updateOrCreate(['key' => 'google_sheets_test_students_tab'], ['value' => '']);

        $tab = GoogleSheetService::getSheetTab('students', 'Студенти');
        $this->assertEquals('Головний_Аркуш', $tab);
    }

    public function test_get_sheet_url_generates_valid_google_sheets_url(): void
    {
        Setting::updateOrCreate(['key' => 'google_sheets_production_students_id'], ['value' => 'test-sheet-url-id']);

        $url = GoogleSheetService::getSheetUrl('students');
        $this->assertEquals('https://docs.google.com/spreadsheets/d/test-sheet-url-id/edit?usp=sharing', $url);
    }
}
