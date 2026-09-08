<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Orchid\Screens\Setting\GoogleSheetsSettingsScreen;
use App\Services\GoogleSheet\GoogleSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class GoogleSheetsSettingsScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_screen_query_returns_expected_default_structure(): void
    {
        $screen = new GoogleSheetsSettingsScreen();
        $data = $screen->query();

        $this->assertArrayHasKey('google_sheets_test_mode', $data);
        $this->assertArrayHasKey('google_sheets_production_students_id', $data);
        $this->assertArrayHasKey('google_sheets_production_students_tab', $data);
        $this->assertArrayHasKey('google_sheets_test_students_id', $data);
        $this->assertArrayHasKey('google_sheets_test_students_tab', $data);
        $this->assertArrayHasKey('google_sheets_production_subjects_id', $data);
        $this->assertArrayHasKey('google_sheets_production_subjects_tab', $data);
        $this->assertArrayHasKey('google_sheets_test_subjects_id', $data);
        $this->assertArrayHasKey('google_sheets_test_subjects_tab', $data);

        $this->assertEquals(GoogleSheetService::DEFAULT_STUDENTS_SHEET_ID, $data['google_sheets_production_students_id']);
        $this->assertEquals('Студенти', $data['google_sheets_production_students_tab']);
    }

    public function test_screen_save_method_persists_settings(): void
    {
        $screen = new GoogleSheetsSettingsScreen();

        $request = new Request([
            'google_sheets_test_mode'               => '1',
            'google_sheets_production_students_id'  => 'prod-stu-123',
            'google_sheets_production_students_tab' => 'Картки',
            'google_sheets_test_students_id'        => 'test-stu-456',
            'google_sheets_test_students_tab'       => 'ТестовийАркуш',
            'google_sheets_production_subjects_id'  => 'prod-sub-789',
            'google_sheets_production_subjects_tab' => 'Каталог',
            'google_sheets_test_subjects_id'        => 'test-sub-000',
            'google_sheets_test_subjects_tab'       => 'ТестКаталог',
        ]);

        $screen->save($request);

        $this->assertDatabaseHas('settings', [
            'key' => 'google_sheets_test_mode',
            'value' => '1',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'google_sheets_production_students_id',
            'value' => 'prod-stu-123',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'google_sheets_production_students_tab',
            'value' => 'Картки',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'google_sheets_test_students_id',
            'value' => 'test-stu-456',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'google_sheets_test_students_tab',
            'value' => 'ТестовийАркуш',
        ]);

        // Verify service picks up the new saved settings
        $this->assertTrue(GoogleSheetService::isTestMode());
        $this->assertEquals('test-stu-456', GoogleSheetService::getSheetId('students'));
        $this->assertEquals('ТестовийАркуш', GoogleSheetService::getSheetTab('students'));
    }
}
