<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserSpecialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSpecialtyDateNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalizes_various_valid_date_formats(): void
    {
        $user = User::factory()->create();

        $specialty = new UserSpecialty();
        $specialty->user_id = $user->id;
        $specialty->email = $user->email;
        $specialty->card_id = 'CARD-100';

        // 1. Slash format: m/d/Y
        $specialty->birth_date = '5/31/1996';
        $this->assertEquals('1996-05-31', $specialty->birth_date);

        // 2. Future date slash format: 1/16/2033
        $specialty->valid_until = '1/16/2033';
        $this->assertEquals('2033-01-16', $specialty->valid_until);

        // 3. Dot format: d.m.Y
        $specialty->study_start = '01.09.2023';
        $this->assertEquals('2023-09-01', $specialty->study_start);

        // 4. Standard MySQL format: Y-m-d
        $specialty->study_end = '2027-06-30';
        $this->assertEquals('2027-06-30', $specialty->study_end);

        // 5. Next level admission date: 12/31/2027
        $specialty->next_level_admission_date = '12/31/2027';
        $this->assertEquals('2027-12-31', $specialty->next_level_admission_date);

        // 6. Datetime string with hours/minutes
        $specialty->issue_date = '2025-08-15 14:30:00';
        $this->assertEquals('2025-08-15', $specialty->issue_date);
    }

    public function test_handles_invalid_dates_and_placeholders_gracefully(): void
    {
        $specialty = new UserSpecialty();

        // 1. Question mark placeholder '?'
        $specialty->birth_date = '?';
        $this->assertNull($specialty->birth_date);

        // 2. Empty string
        $specialty->study_start = '';
        $this->assertNull($specialty->study_start);

        // 3. Whitespaces only
        $specialty->valid_until = '   ';
        $this->assertNull($specialty->valid_until);

        // 4. Arbitrary non-date text
        $specialty->study_end = 'невідомо';
        $this->assertNull($specialty->study_end);

        // 5. Null
        $specialty->next_level_admission_date = null;
        $this->assertNull($specialty->next_level_admission_date);
    }
}
