<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\GroupSemesterLimit;
use App\Models\User;
use App\Models\UserSpecialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGroupAssociationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_exist_without_group(): void
    {
        $user = User::factory()->create();

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-NO-GROUP',
            'full_name' => 'Студент Без Групи',
            'group_id' => null,
            'group_name' => null,
        ]);

        $this->assertNull($specialty->group_id);
        $this->assertNull($specialty->group);
        $this->assertNull($specialty->group?->semesterLimits);
    }

    public function test_student_with_group_loads_semester_limits_correctly(): void
    {
        $user = User::factory()->create();

        $group = Group::create([
            'name' => 'ІПЗ-21',
            'semester_count' => 8,
        ]);

        GroupSemesterLimit::create([
            'group_id' => $group->id,
            'semester' => 1,
            'max_subjects' => 3,
        ]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-WITH-GROUP',
            'full_name' => 'Студент З Групою',
            'group_id' => $group->id,
            'group_name' => $group->name,
        ]);

        $this->assertNotNull($specialty->group);
        $this->assertEquals('ІПЗ-21', $specialty->group->name);
        $this->assertEquals(8, $specialty->group->semester_count);
        $this->assertEquals(3, $specialty->group->semesterLimits->firstWhere('semester', 1)->max_subjects);
    }
}
