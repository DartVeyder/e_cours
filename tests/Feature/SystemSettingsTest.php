<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Orchid\Screens\Setting\SettingsScreen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_screen_query_retrieves_selection_state(): void
    {
        Setting::updateOrCreate(['key' => 'subject_selection_enabled'], ['value' => '0']);

        $screen = new SettingsScreen();
        $query = $screen->query();

        $this->assertArrayHasKey('subject_selection_enabled', $query);
        $this->assertEquals('0', $query['subject_selection_enabled']);
    }

    public function test_settings_screen_save_updates_selection_state(): void
    {
        $screen = new SettingsScreen();

        $request = new Request([
            'subject_selection_enabled' => true,
        ]);

        $screen->save($request);

        $this->assertDatabaseHas('settings', [
            'key' => 'subject_selection_enabled',
            'value' => '1',
        ]);
    }
}
