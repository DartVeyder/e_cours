<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_setting(): void
    {
        Setting::create([
            'key' => 'custom_system_param',
            'value' => 'active_value',
        ]);

        $value = Setting::where('key', 'custom_system_param')->value('value');
        $this->assertEquals('active_value', $value);
    }

    public function test_update_or_create_updates_existing_setting(): void
    {
        Setting::updateOrCreate(['key' => 'toggle_feature'], ['value' => '0']);
        $this->assertEquals('0', Setting::where('key', 'toggle_feature')->value('value'));

        Setting::updateOrCreate(['key' => 'toggle_feature'], ['value' => '1']);
        $this->assertEquals('1', Setting::where('key', 'toggle_feature')->value('value'));
        $this->assertEquals(1, Setting::where('key', 'toggle_feature')->count());
    }
}
