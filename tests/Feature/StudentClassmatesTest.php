<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\UserSpecialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentClassmatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_classmates_screen(): void
    {
        $response = $this->get(route('platform.classmates'));
        $response->assertRedirect('/login');
    }

    public function test_student_views_their_group_classmates(): void
    {
        $groupA = Group::create(['name' => 'ІПЗ-21']);
        $groupB = Group::create(['name' => 'ФІЛ-11']);

        $userStudent = User::factory()->create([
            'name' => 'Іваненко Іван',
            'email' => 'ivanenko@dspu.edu.ua',
            'permissions' => ['platform.index' => true],
        ]);

        $studentCard = UserSpecialty::create([
            'user_id' => $userStudent->id,
            'card_id' => 'CARD-001',
            'full_name' => 'Іваненко Іван',
            'email' => 'ivanenko@dspu.edu.ua',
            'group_name' => 'ІПЗ-21',
            'group_id' => $groupA->id,
            'specialty' => '121 Інженерія програмного забезпечення',
            'education_program' => 'Інженерія ПЗ',
            'department' => 'Факультет фізики, математики, економіки та інноваційних технологій',
            'degree' => 'Бакалавр',
            'study_form' => 'Денна',
            'rnokpp' => '1234567890',
            'birth_date' => '2004-05-15',
        ]);

        // Classmate in the same group
        $classmateUser = User::factory()->create(['name' => 'Петренко Петро', 'email' => 'petrenko@dspu.edu.ua']);
        $classmate = UserSpecialty::create([
            'user_id' => $classmateUser->id,
            'card_id' => 'CARD-002',
            'full_name' => 'Петренко Петро',
            'email' => 'petrenko@dspu.edu.ua',
            'group_name' => 'ІПЗ-21',
            'group_id' => $groupA->id,
            'specialty' => '121 Інженерія програмного забезпечення',
            'education_program' => 'Інженерія ПЗ',
            'department' => 'Факультет фізики, математики, економіки та інноваційних технологій',
            'degree' => 'Бакалавр',
            'study_form' => 'Денна',
            'rnokpp' => '9876543210',
            'birth_date' => '2004-09-20',
        ]);

        // Student in another group
        $otherStudent = UserSpecialty::create([
            'user_id' => null,
            'card_id' => 'CARD-003',
            'full_name' => 'Сидоренко Сидір',
            'email' => 'sydorenko@dspu.edu.ua',
            'group_name' => 'ФІЛ-11',
            'group_id' => $groupB->id,
            'specialty' => '035 Філологія',
            'education_program' => 'Германські мови',
            'department' => 'Факультет філології',
            'degree' => 'Бакалавр',
            'study_form' => 'Денна',
            'rnokpp' => '5555555555',
            'birth_date' => '2003-12-01',
        ]);

        // Attach an elective subject to classmate
        $subject = \App\Models\Subject::create(['name' => 'Хмарні технології', 'active' => 1]);
        $classmate->subjects()->attach($subject->id, [
            'user_id' => $classmateUser->id,
            'semester' => 4,
            'is_student_choice' => 1,
        ]);

        $response = $this->actingAs($userStudent)->get(route('platform.classmates'));

        $response->assertOk();
        $response->assertSee('Академічна група: ІПЗ-21');
        $response->assertSee('Список одногрупників');
        $response->assertSee('Іваненко Іван');
        $response->assertSee('Петренко Петро');
        $response->assertSee('petrenko@dspu.edu.ua');
        $response->assertSee('121 Інженерія програмного забезпечення');

        // Verify classmate's chosen subject is displayed
        $response->assertSee('Хмарні технології');
        $response->assertSee('4 семестр');

        // Verify other group's student is NOT visible (IDOR / data isolation)
        $response->assertDontSee('Сидоренко Сидір');
        $response->assertDontSee('sydorenko@dspu.edu.ua');
        $response->assertDontSee('ФІЛ-11');

        // Verify sensitive PII is NEVER displayed
        $response->assertDontSee('1234567890');
        $response->assertDontSee('9876543210');
        $response->assertDontSee('2004-05-15');
        $response->assertDontSee('2004-09-20');
    }

    public function test_student_without_group_sees_informative_empty_state(): void
    {
        $userStudent = User::factory()->create([
            'name' => 'Новий Студент',
            'email' => 'newstudent@dspu.edu.ua',
            'permissions' => ['platform.index' => true],
        ]);

        UserSpecialty::create([
            'user_id' => $userStudent->id,
            'card_id' => 'CARD-004',
            'full_name' => 'Новий Студент',
            'email' => 'newstudent@dspu.edu.ua',
            'group_name' => null,
            'group_id' => null,
            'specialty' => '121 Інженерія програмного забезпечення',
        ]);

        $response = $this->actingAs($userStudent)->get(route('platform.classmates'));

        $response->assertOk();
        $response->assertSee('Академічну групу ще не призначено');
        $response->assertSee('Повернутися на головну');
    }
}
