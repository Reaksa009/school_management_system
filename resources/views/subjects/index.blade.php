@extends('layouts.app')

@section('title', 'គ្រប់គ្រងមុខវិជ្ជា')
@section('subtitle', 'មុខវិជ្ជាតាមថ្នាក់ និងគ្រូបង្រៀន')

@section('actions')
    @if (auth()->user()->role === 'admin')
        <a class="btn" href="{{ route('subjects.create') }}"><i data-lucide="plus"></i> បន្ថែមមុខវិជ្ជា</a>
    @endif
@endsection

@section('content')
    <section class="table-panel">
        <form class="toolbar" method="GET">
            <div class="filters">
                <div class="field">
                    <label>ស្វែងរក</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="លេខកូដ ឬឈ្មោះ">
                </div>
                <div class="field">
                    <label>ថ្នាក់</label>
                    <select name="class_id">
                        <option value="">ថ្នាក់ទាំងអស់</option>
                        @foreach ($classes as $classRoom)
                            <option value="{{ $classRoom->id }}" @selected(request('class_id') == $classRoom->id)>{{ $classRoom->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn secondary" type="submit"><i data-lucide="search"></i> ស្វែងរក</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>លេខកូដ</th>
                    <th>មុខវិជ្ជា</th>
                    <th>ថ្នាក់</th>
                    <th>គ្រូបង្រៀន</th>
                    <th>ម៉ោងសិក្សា</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjects as $subject)
                    <tr>
                        <td>{{ $subject->code }}</td>
                        <td><strong>{{ $subject->name }}</strong></td>
                        <td>{{ $subject->classRoom?->name ?? '-' }}</td>
                        <td>{{ $subject->teacher?->full_name ?? '-' }}</td>
                        <td>{{ $subject->credit_hours }}</td>
                        <td>
                            @if (auth()->user()->role === 'admin')
                                <div class="actions-row">
                                    <a class="icon-btn" href="{{ route('subjects.edit', $subject) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                    <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('តើអ្នកចង់លុបមុខវិជ្ជានេះមែនទេ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn danger" type="submit" title="លុប"><i data-lucide="trash-2"></i></button>
                                    </form>
                                </div>
                            @else
                                <span class="muted">មើលប៉ុណ្ណោះ</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">មិនទាន់មានទិន្នន័យមុខវិជ្ជា</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $subjects->links() }}</div>
    </section>
@endsection
