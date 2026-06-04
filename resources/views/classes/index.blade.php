@extends('layouts.app')

@section('title', 'គ្រប់គ្រងថ្នាក់')
@section('subtitle', 'បង្កើតថ្នាក់ កំណត់ឆ្នាំសិក្សា និងគ្រូទទួលខុសត្រូវ')

@section('actions')
    @if (auth()->user()->role === 'admin')
        <a class="btn" href="{{ route('classes.create') }}"><i data-lucide="plus"></i> បន្ថែមថ្នាក់</a>
    @endif
@endsection

@section('content')
    <section class="table-panel">
        <form class="toolbar" method="GET">
            <div class="field">
                <label>ស្វែងរក</label>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="ឈ្មោះថ្នាក់ ផ្នែក ឬបន្ទប់">
            </div>
            <button class="btn secondary" type="submit"><i data-lucide="search"></i> ស្វែងរក</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ថ្នាក់</th>
                    <th>ផ្នែក</th>
                    <th>ឆ្នាំសិក្សា</th>
                    <th>បន្ទប់</th>
                    <th>គ្រូទទួលខុសត្រូវ</th>
                    <th>ចំនួនសិស្ស</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $classRoom)
                    <tr>
                        <td><strong>{{ $classRoom->name }}</strong></td>
                        <td>{{ $classRoom->section ?? '-' }}</td>
                        <td>{{ $classRoom->academic_year ?? '-' }}</td>
                        <td>{{ $classRoom->room ?? '-' }}</td>
                        <td>{{ $classRoom->teacher?->full_name ?? '-' }}</td>
                        <td>{{ $classRoom->students_count ?? $classRoom->students->count() }}</td>
                        <td>
                            @if (auth()->user()->role === 'admin')
                                <div class="actions-row">
                                    <a class="icon-btn" href="{{ route('classes.edit', $classRoom) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                    <form method="POST" action="{{ route('classes.destroy', $classRoom) }}" onsubmit="return confirm('តើអ្នកចង់លុបថ្នាក់នេះមែនទេ?')">
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
                    <tr><td colspan="7" class="empty">មិនទាន់មានទិន្នន័យថ្នាក់</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $classes->links() }}</div>
    </section>
@endsection
