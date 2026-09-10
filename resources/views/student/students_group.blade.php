<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">
        <thead>
        <tr>
            <th class="text-center" style="width: 50px;">№</th>
            <th class="text-center" style="width: 120px;">Рік вступу</th>
            <th>Студент</th>
            @if($students->first()?->semesterData)
                @foreach($students->first()->semesterData as $semester => $data)
                    <th class="text-center">Семестр {{ $semester }}</th>
                @endforeach
            @endif
            <th class="text-center">Всього обрано</th>
        </tr>
        </thead>
        <tbody>
        @foreach($students as $index => $student)
            @php
                $year = $student->entry_year ?? ($student->study_start ? substr((string)$student->study_start, 0, 4) : '—');
                $badgeClass = ($year == '2026') ? 'bg-success' : 'bg-primary';
            @endphp
            <tr>
                <td class="text-center text-muted">{{ $index + 1 }}</td>
                <td class="text-center">
                    @if($year !== '—' && !empty($year))
                        <span class="badge {{ $badgeClass }} bg-opacity-75 text-white fw-semibold px-2 py-1 rounded-pill">{{ $year }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <th scope="row" class="bg-light">{{ $student->full_name }}</th>
                @foreach($student->semesterData as $semester => $data)
                    <td class="text-center">{{ $data['selected'] }}/{{ $data['max'] }}</td>
                @endforeach
                <td class="text-center fw-bold">{{ $student->totalSelected }}</td> <!-- загальна кількість -->
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

