<div class="container-fluid p-0">
    @if($studentSpecialty && (!empty($studentSpecialty->group_name) || !empty($studentSpecialty->group_id)))
        {{-- Group Summary Banner --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-white" style="width: 56px; height: 56px; font-size: 26px;">
                                👥
                            </div>
                            <div>
                                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 fw-semibold mb-1">
                                    Академічна група
                                </span>
                                <h3 class="fw-bold mb-1 text-white">
                                    {{ $studentSpecialty->group_name }}
                                </h3>
                                <p class="mb-0 text-white-50 small">
                                    {{ $studentSpecialty->specialty }} 
                                    @if($studentSpecialty->department) • {{ $studentSpecialty->department }} @endif
                                    @if($studentSpecialty->degree) • {{ $studentSpecialty->degree }} @endif
                                    @if($studentSpecialty->study_form) ({{ $studentSpecialty->study_form }}) @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-inline-flex flex-column align-items-md-end bg-white bg-opacity-10 rounded-3 p-3">
                            <span class="text-white-50 small text-uppercase fw-semibold">Кількість студентів</span>
                            <span class="fs-2 fw-bold text-white">{{ $classmates->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Classmates Search and Table Card --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 p-4 pb-0">
                <div class="row g-3 align-items-center justify-content-between">
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            </svg>
                            Список одногрупників
                        </h5>
                        <small class="text-muted">Знайдіть контактні дані одногрупників для навчання та спільних проєктів</small>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="position-relative">
                            <input type="text" 
                                   id="classmateSearchInput" 
                                   class="form-control rounded-pill ps-4 py-2 border shadow-sm" 
                                   placeholder="🔍 Пошук за ПІБ або поштою..."
                                   oninput="filterClassmatesTable(this.value)">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                @if($classmates->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="classmatesMainTable">
                            <thead class="table-light">
                                <tr class="text-uppercase text-muted small">
                                    <th class="text-center" style="width: 50px;">№</th>
                                    <th>Студент</th>
                                    <th>Освітня програма / Спеціальність</th>
                                    <th>Форма навчання</th>
                                    <th>Корпоративна пошта</th>
                                    <th class="text-center" style="width: 170px;">Вибіркові дисципліни</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classmates as $index => $classmate)
                                    @php
                                        $isSelf = ($classmate->id === $studentSpecialty->id);
                                    @endphp
                                    <tr class="classmate-item-row {{ $isSelf ? 'table-primary bg-opacity-25' : '' }}">
                                        <td class="text-center text-muted fw-semibold">
                                            {{ $index + 1 }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold {{ $isSelf ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' }}" style="width: 38px; height: 38px; font-size: 14px; min-width: 38px;">
                                                    {{ mb_substr($classmate->full_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark student-name-text">{{ $classmate->full_name }}</span>
                                                    @if($isSelf)
                                                        <span class="badge bg-primary text-white rounded-pill ms-2 px-2 py-0" style="font-size: 11px;">Ви</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                {{ $classmate->education_program ?? $classmate->specialty ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">
                                                {{ $classmate->degree ?? 'Бакалавр' }} / {{ $classmate->study_form ?? 'Денна' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($classmate->email)
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <a href="mailto:{{ $classmate->email }}" class="text-decoration-none d-inline-flex align-items-center gap-1 text-primary fw-semibold student-email-text" title="Написати листа">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                                                        </svg>
                                                        {{ $classmate->email }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($classmate->subjects && $classmate->subjects->isNotEmpty())
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-sm"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#screen-classmate-subj-{{ $classmate->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="screen-classmate-subj-{{ $classmate->id }}"
                                                        title="Натисніть для перегляду обраних дисциплін">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-book-half" viewBox="0 0 16 16">
                                                        <path d="M8.5 2.687c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
                                                    </svg>
                                                    <span>{{ $classmate->subjects->count() }} @if($classmate->subjects->count() == 1) предм. @else предм. @endif</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="badge bg-light text-muted border px-2 py-1 rounded-pill small">Не обрано</span>
                                            @endif
                                        </td>
                                    </tr>

                                    @if($classmate->subjects && $classmate->subjects->isNotEmpty())
                                        <tr id="screen-classmate-subj-{{ $classmate->id }}" class="collapse classmate-subjects-row bg-light">
                                            <td colspan="6" class="p-3">
                                                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                                            <span class="text-primary fs-5">📚</span>
                                                            Обрані вибіркові дисципліни студента: <span class="text-primary">{{ $classmate->full_name }}</span>
                                                        </h6>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1">
                                                            Всього обрано: {{ $classmate->subjects->count() }}
                                                        </span>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover align-middle mb-0">
                                                            <thead class="table-light small text-muted text-uppercase">
                                                                <tr>
                                                                    <th style="width: 140px;">Семестр</th>
                                                                    <th>Назва дисципліни</th>
                                                                    <th>Кафедра / Підрозділ</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($classmate->subjects as $subj)
                                                                    <tr>
                                                                        <td>
                                                                            @if($subj->pivot->semester)
                                                                                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold">
                                                                                    {{ $subj->pivot->semester }} семестр
                                                                                </span>
                                                                            @else
                                                                                <span class="text-muted small">—</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <span class="fw-semibold text-dark">{{ $subj->name }}</span>
                                                                        </td>
                                                                        <td>
                                                                            <span class="small text-muted">{{ $subj->chair ?? $subj->department ?? '—' }}</span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <div class="fs-1 mb-2">👥</div>
                        <h5>Одногрупників не знайдено</h5>
                        <p class="small">У цій академічній групі наразі немає інших зареєстрованих студентів.</p>
                    </div>
                @endif
            </div>
        </div>

        <script>
            function filterClassmatesTable(query) {
                var term = query.toLowerCase().trim();
                var rows = document.querySelectorAll('#classmatesMainTable tbody tr.classmate-item-row');
                rows.forEach(function(row) {
                    var name = row.querySelector('.student-name-text')?.textContent?.toLowerCase() || '';
                    var email = row.querySelector('.student-email-text')?.textContent?.toLowerCase() || '';
                    var nextRow = row.nextElementSibling;
                    if (!term || name.includes(term) || email.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                        if (nextRow && nextRow.classList.contains('classmate-subjects-row')) {
                            nextRow.style.display = 'none';
                            nextRow.classList.remove('show');
                        }
                    }
                });
            }
        </script>
    @else
        {{-- Empty state when student has no group assigned --}}
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="mx-auto mb-3 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary" style="width: 72px; height: 72px; font-size: 32px;">
                👥
            </div>
            <h4 class="fw-bold text-dark mb-2">Академічну групу ще не призначено</h4>
            <p class="text-muted mx-auto" style="max-width: 500px;">
                Ваш профіль наразі не прив'язаний до академічної групи. Якщо ви вважаєте це помилкою, будь ласка, зверніться до деканату або адміністратора системи.
            </p>
            <div class="mt-3">
                <a href="{{ route('platform.main') }}" class="btn btn-primary rounded-pill px-4">
                    Повернутися на головну
                </a>
            </div>
        </div>
    @endif
</div>
