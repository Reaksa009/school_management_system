@extends('layouts.app')

@section('title', 'វត្តមាន/អវត្តមាន')
@section('subtitle', 'កត់ត្រា និងតាមដានវត្តមានសិស្សប្រចាំថ្ងៃ')

@section('actions')
    @if (auth()->user()->hasAnyRole(['admin', 'teacher']))
        <a class="btn" href="{{ route('attendances.create') }}"><i data-lucide="plus"></i> កត់ត្រាវត្តមាន</a>
    @endif
@endsection

@section('content')
    <section class="table-panel">
        <form class="toolbar" method="GET">
            <div class="filters">
                <div class="field">
                    <label>កាលបរិច្ឆេទ</label>
                    <input type="date" name="date" value="{{ request('date') }}">
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
                <div class="field">
                    <label>ស្ថានភាព</label>
                    <select name="status">
                        <option value="">ទាំងអស់</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn secondary" type="submit"><i data-lucide="filter"></i> ចម្រោះ</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>កាលបរិច្ឆេទ</th>
                    <th>សិស្ស</th>
                    <th>ថ្នាក់</th>
                    <th>មុខវិជ្ជា</th>
                    <th>គ្រូ</th>
                    <th>ស្ថានភាព</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->attendance_date?->format('Y-m-d') }}</td>
                        <td>{{ $attendance->student?->full_name }}</td>
                        <td>{{ $attendance->classRoom?->name ?? $attendance->student?->classRoom?->name ?? '-' }}</td>
                        <td>{{ $attendance->subject?->name ?? '-' }}</td>
                        <td>{{ $attendance->teacher?->full_name ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $attendance->status === 'absent' ? 'danger' : ($attendance->status === 'late' ? 'warning' : 'success') }}">
                                {{ $attendance->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            @if (auth()->user()->hasAnyRole(['admin', 'teacher']))
                                <div class="actions-row">
                                    <a class="icon-btn" href="{{ route('attendances.edit', $attendance) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                    <form method="POST" action="{{ route('attendances.destroy', $attendance) }}" onsubmit="return confirm('តើអ្នកចង់លុបកំណត់ត្រានេះមែនទេ?')">
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
                    <tr><td colspan="7" class="empty">មិនទាន់មានទិន្នន័យវត្តមាន</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $attendances->links() }}</div>
    </section>
@endsection
