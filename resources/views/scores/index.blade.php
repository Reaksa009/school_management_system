@extends('layouts.app')

@section('title', 'គ្រប់គ្រងពិន្ទុ')
@section('subtitle', 'ជ្រើសការប្រឡង ដើម្បីបញ្ចូល ឬកែប្រែពិន្ទុសិស្ស')

@section('content')
    <section class="table-panel">
        <table>
            <thead>
                <tr>
                    <th>ការប្រឡង</th>
                    <th>ថ្នាក់</th>
                    <th>មុខវិជ្ជា</th>
                    <th>ថ្ងៃប្រឡង</th>
                    <th>ចំនួនពិន្ទុ</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr>
                        <td><strong>{{ $exam->title }}</strong></td>
                        <td>{{ $exam->classRoom?->name ?? '-' }}</td>
                        <td>{{ $exam->subject?->name ?? '-' }}</td>
                        <td>{{ $exam->exam_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ $exam->scores->count() }}</td>
                        <td><a class="btn secondary" href="{{ route('scores.edit', $exam) }}"><i data-lucide="pencil"></i> បញ្ចូលពិន្ទុ</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">សូមបង្កើតការប្រឡងជាមុនសិន</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $exams->links() }}</div>
    </section>
@endsection
