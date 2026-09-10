<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupSemesterLimit;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecialtyAutoSelectionAndWarningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create(['key' => 'subject_selection_enabled', 'value' => '1']);
    }

    public function test_single_specialty_is_auto_selected_on_selsubject_screen(): void
    {
        $user = User::factory()->create([
            'name' => 'Студент Один',
            'permissions' => ['platform.index' => true],
        ]);

        $group = Group::create(['name' => 'ІПЗ-21']);
        GroupSemesterLimit::create(['group_id' => $group->id, 'semester' => 1, 'max_subjects' => 3]);

        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-111',
            'full_name' => 'Студент Один',
            'group_name' => 'ІПЗ-21',
            'group_id' => $group->id,
            'specialty' => '121 Інженерія програмного забезпечення',
            'degree' => 'Бакалавр',
        ]);

        $subject = Subject::create([
            'name' => 'Штучний інтелект',
            'active' => 1,
            'education_level' => 'Бакалавр',
        ]);

        $response = $this->actingAs($user)->get(route('platform.selsubjects'));

        $response->assertOk();
        $response->assertCookie('user_specialty_id', (string) $specialty->id);
        $response->assertSee('121 Інженерія програмного забезпечення');
        $response->assertSee('Семестр 1: 0/3');
        $response->assertDontSee('⚠️ Спеціальність не обрана!');
    }

    public function test_multiple_specialties_show_warning_when_not_selected_on_selsubject_screen(): void
    {
        $user = User::factory()->create([
            'name' => 'Студент Мульти',
            'permissions' => ['platform.index' => true],
        ]);

        $group1 = Group::create(['name' => 'ІПЗ-21']);
        $group2 = Group::create(['name' => 'КН-21']);

        $spec1 = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-M1',
            'full_name' => 'Студент Мульти',
            'group_name' => 'ІПЗ-21',
            'group_id' => $group1->id,
            'specialty' => '121 Інженерія програмного забезпечення',
            'degree' => 'Бакалавр',
        ]);

        $spec2 = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-M2',
            'full_name' => 'Студент Мульти',
            'group_name' => 'КН-21',
            'group_id' => $group2->id,
            'specialty' => '122 Комп\'ютерні науки',
            'degree' => 'Бакалавр',
        ]);

        Subject::create([
            'name' => 'Хмарні обчислення',
            'active' => 1,
            'education_level' => 'Бакалавр',
        ]);

        $response = $this->actingAs($user)->get(route('platform.selsubjects'));

        $response->assertOk();
        $response->assertSee('⚠️ Спеціальність не обрана!');
        $response->assertSee('Знайдено 2 спеціальності');
        $response->assertSee('Без вибору спеціальності неможливо здійснювати вибір вибіркових освітніх компонентів');
        $response->assertSee('121 Інженерія програмного забезпечення');
        $response->assertSee('122 Комп\'ютерні науки');
        $response->assertSee('Хмарні обчислення');
        $response->assertSee('Оберіть спеціальність');
    }

    public function test_no_specialties_show_warning_on_selsubject_screen(): void
    {
        $user = User::factory()->create([
            'name' => 'Користувач Без Картки',
            'permissions' => ['platform.index' => true],
        ]);

        $response = $this->actingAs($user)->get(route('platform.selsubjects'));

        $response->assertOk();
        $response->assertSee('Картку здобувача не знайдено');
    }

    public function test_single_specialty_is_auto_selected_on_main_screen(): void
    {
        $user = User::factory()->create([
            'name' => 'Студент Авто',
            'permissions' => ['platform.index' => true],
        ]);

        $group = Group::create(['name' => 'ІПЗ-21']);
        $specialty = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-AUTO',
            'full_name' => 'Студент Авто',
            'group_name' => 'ІПЗ-21',
            'group_id' => $group->id,
            'specialty' => '121 Інженерія програмного забезпечення',
        ]);

        $response = $this->actingAs($user)->get(route('platform.main'));

        $response->assertOk();
        $response->assertCookie('user_specialty_id', (string) $specialty->id);
        $response->assertSee('Дані студента');
        $response->assertSee('121 Інженерія програмного забезпечення');
        $response->assertDontSee('⚠️ Спеціальність не обрана!');
    }

    public function test_multiple_specialties_show_warning_on_main_screen(): void
    {
        $user = User::factory()->create([
            'name' => 'Студент Дві Спеціальності',
            'permissions' => ['platform.index' => true],
        ]);

        $group1 = Group::create(['name' => 'ІПЗ-21']);
        $group2 = Group::create(['name' => 'КН-21']);

        UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-D1',
            'full_name' => 'Студент Дві Спеціальності',
            'group_name' => 'ІПЗ-21',
            'group_id' => $group1->id,
            'specialty' => '121 Інженерія програмного забезпечення',
        ]);

        UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-D2',
            'full_name' => 'Студент Дві Спеціальності',
            'group_name' => 'КН-21',
            'group_id' => $group2->id,
            'specialty' => '122 Комп\'ютерні науки',
        ]);

        $response = $this->actingAs($user)->get(route('platform.main'));

        $response->assertOk();
        $response->assertSee('⚠️ Спеціальність не обрана!');
        $response->assertSee('Доступно спеціальностей: 2');
        $response->assertSee('121 Інженерія програмного забезпечення');
        $response->assertSee('122 Комп\'ютерні науки');
    }

    public function test_user_can_choose_specialty_via_screen_method(): void
    {
        $user = User::factory()->create([
            'name' => 'Студент Вибір',
            'permissions' => ['platform.index' => true],
        ]);

        $group = Group::create(['name' => 'ІПЗ-21']);
        $spec = UserSpecialty::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'card_id' => 'CARD-CHOOSE',
            'full_name' => 'Студент Вибір',
            'group_name' => 'ІПЗ-21',
            'group_id' => $group->id,
            'specialty' => '121 Інженерія програмного забезпечення',
        ]);

        $response = $this->actingAs($user)->post(route('platform.selsubjects') . '/chooseSpecialty', [
            'id' => $spec->id,
            'text' => '121 Інженерія програмного забезпечення (ІПЗ-21)',
        ]);

        $response->assertCookie('user_specialty_id', (string) $spec->id);
    }
}
