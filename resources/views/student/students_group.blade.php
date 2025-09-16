<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <tbody>
        @foreach($students as $student)
            <tr>
                <th scope="row" class="bg-light">{{ $student->full_name }}</th>
                @foreach($student->subjects as $subject)
                    <td>{{ $subject->name }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
