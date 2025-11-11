<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th>Студент</th>
            @if($students->first()?->semesterData)
                @foreach($students->first()->semesterData as $semester => $data)
                    <th>Семестр {{ $semester }}</th>
                @endforeach
            @endif
            <th>Всього обрано</th>
        </tr>
        </thead>
        <tbody>
        @foreach($students as $student)
            <tr>
                <th scope="row" class="bg-light">{{ $student->full_name }}</th>
                @foreach($student->semesterData as $semester => $data)
                    <td>{{ $data['selected'] }}/{{ $data['max'] }}</td>
                @endforeach
                <td>{{ $student->totalSelected }}</td> <!-- загальна кількість -->
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
