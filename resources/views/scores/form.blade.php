@extends('layouts.app')

@section('title', 'បញ្ចូលពិន្ទុ')
@section('subtitle', $exam->title.' · '.$exam->classRoom?->name.' · '.$exam->subject?->name)

@section('content')
    <form method="POST" action="{{ route('scores.update', $exam) }}" class="table-panel">
        @csrf
        @method('PUT')

        <table>
            <thead>
                <tr>
                    <th>លេខសម្គាល់</th>
                    <th>សិស្ស</th>
                    <th>ពិន្ទុ / {{ $exam->total_marks }}</th>
                    <th>និទ្ទេស</th>
                    <th>ចំណាំ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    @php $score = $scores->get($student->id); @endphp
                    <tr>
                        <td>{{ $student->student_code }}</td>
                        <td><strong>{{ $student->full_name }}</strong></td>
                        <td><input type="number" step="0.01" min="0" max="{{ $exam->total_marks }}" name="scores[{{ $student->id }}][marks]" value="{{ old("scores.{$student->id}.marks", $score?->marks) }}"></td>
                        <td>{{ $score?->grade ?? '-' }}</td>
                        <td><input name="scores[{{ $student->id }}][note]" value="{{ old("scores.{$student->id}.note", $score?->note) }}"></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">មិនមានសិស្សក្នុងថ្នាក់នេះ</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="toolbar">
            <div class="muted">ទុកពិន្ទុឱ្យទទេ ដើម្បីលុបពិន្ទុសិស្សនោះ។</div>
            <div class="actions-row">
                <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុកពិន្ទុ</button>
                <a class="btn secondary" href="{{ route('scores.index') }}">ត្រឡប់ក្រោយ</a>
            </div>
        </div>
    </form>
@endsection
