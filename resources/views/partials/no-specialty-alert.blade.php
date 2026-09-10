<style>
    /* Override Orchid's .h-100 rule on this layout block to prevent full-screen vertical stretching */
    .no-specialty-alert-wrapper,
    .no-specialty-alert-wrapper.h-100,
    div.h-100:has(.no-specialty-alert-wrapper),
    fieldset.h-100:has(.no-specialty-alert-wrapper),
    section.h-100:has(.no-specialty-alert-wrapper),
    main.h-100:has(.no-specialty-alert-wrapper) {
        height: auto !important;
        min-height: 0 !important;
        flex: 0 0 auto !important;
    }
</style>

<div class="no-specialty-alert-wrapper mb-3" style="height: auto !important; min-height: 0 !important; flex: 0 0 auto !important;">
    @if(empty($userSpecialties) || $userSpecialties->isEmpty())
        <div class="alert alert-warning border-0 shadow-sm rounded-3 p-3 m-0" style="background: #fff8e1; border-left: 5px solid #ff9800 !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="text-warning flex-shrink-0" style="font-size: 1.75rem; line-height: 1;">
                    ⚠️
                </div>
                <div>
                    <strong class="d-block text-dark mb-1">Картку здобувача не знайдено</strong>
                    <span class="text-secondary small">За вашим обліковим записом наразі не закріплено жодної картки спеціальності. Вибір дисциплін неможливий. Зверніться до деканату або адміністратора.</span>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm rounded-3 p-3 m-0" style="background: #fff9e6; border-left: 5px solid #ff9800 !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-warning flex-shrink-0" style="font-size: 1.75rem; line-height: 1;">
                        ⚠️
                    </div>
                    <div>
                        <strong class="d-block text-dark">⚠️ Спеціальність не обрана!</strong>
                        <span class="text-secondary small">Знайдено {{ $userSpecialties->count() }} {{ $userSpecialties->count() == 1 ? 'спеціальність' : 'спеціальності' }}. Без вибору спеціальності неможливо здійснювати вибір вибіркових освітніх компонентів. Оберіть активну спеціальність для вибору дисциплін:</span>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 ms-auto">
                    @foreach($userSpecialties as $spec)
                        <button type="submit" 
                                form="post-form" 
                                formaction="{{ route('platform.selsubjects') }}/chooseSpecialty?id={{ $spec->id }}&text={{ urlencode($spec->specialty . ' (' . ($spec->group_name ?? 'Без групи') . ')') }}" 
                                class="btn btn-warning shadow-sm fw-bold rounded-pill px-3 py-2 text-dark d-inline-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/>
                            </svg>
                            <span>{{ $spec->specialty }} ({{ $spec->group_name ?? 'Без групи' }})</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="mt-2 pt-2 border-top border-warning border-opacity-25 d-flex align-items-center gap-2 text-dark small">
                <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">💡 Інструкція:</span>
                <span>Натисніть кнопку вашої спеціальності або виберіть її у меню зверху для відкриття випадаючих списків вибору семестрів та підрахунку лімітів.</span>
            </div>
        </div>
    @endif
</div>
