@extends('layouts.app')

@section('title', 'ការប្រឡង')
@section('subtitle', 'បង្កើតការប្រឡង និងបញ្ចូលពិន្ទុតាមមុខវិជ្ជា')

@section('actions')
    <a class="btn" href="{{ route('exams.create') }}"><i data-lucide="plus"></i> បង្កើតការប្រឡង</a>
@endsection

@section('content')
    <section class="table-panel">
        <form class="toolbar" method="GET">
            <div class="filters">
                <div class="field">
                    <label>ថ្នាក់</label>
                    <select name="class_id">
                        <option value="">ថ្នាក់ទាំងអស់</option>
                        @foreach ($classes as $classRoom)
                            <option value="{{ $classRoom->id }}" @selected(request('class_id') == $classRoom->id)>{{ $classRoom->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>មុខវិជ្ជា</label>
                    <select name="subject_id">
                        <option value="">មុខវិជ្ជាទាំងអស់</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn secondary" type="submit"><i data-lucide="filter"></i> ចម្រោះ</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ចំណងជើង</th>
                    <th>ថ្នាក់</th>
                    <th>មុខវិជ្ជា</th>
                    <th>វគ្គ/ឆមាស</th>
                    <th>ថ្ងៃប្រឡង</th>
                    <th>ពិន្ទុសរុប</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr>
                        <td><strong>{{ $exam->title }}</strong><br><span class="muted">{{ $exam->scores->count() }} ពិន្ទុបានបញ្ចូល</span></td>
                        <td>{{ $exam->classRoom?->name ?? '-' }}</td>
                        <td>{{ $exam->subject?->name ?? '-' }}</td>
                        <td>{{ $exam->term ?? '-' }}</td>
                        <td>{{ $exam->exam_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ $exam->total_marks }}</td>
                        <td>
                            <div class="actions-row">
                                <a class="icon-btn" href="{{ route('scores.edit', $exam) }}" title="បញ្ចូលពិន្ទុ"><i data-lucide="medal"></i></a>
                                <a class="icon-btn" href="{{ route('exams.edit', $exam) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                <form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('តើអ្នកចង់លុបការប្រឡងនេះមែនទេ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-btn danger" type="submit" title="លុប"><i data-lucide="trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">មិនទាន់មានទិន្នន័យការប្រឡង</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $exams->links() }}</div>
    </section>
@endsection
