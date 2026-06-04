@extends('layouts.app')

@section('title', 'គ្រប់គ្រងសិស្ស')
@section('subtitle', 'ស្វែងរក បន្ថែម និងតាមដានព័ត៌មានសិស្ស')

@section('actions')
    @if (auth()->user()->role === 'admin')
        <a class="btn secondary" href="{{ route('students.import') }}"><i data-lucide="upload"></i> Import CSV</a>
        <a class="btn" href="{{ route('students.create') }}"><i data-lucide="plus"></i> បន្ថែមសិស្ស</a>
    @endif
@endsection

@section('content')
    @if (session('import_errors'))
        <div class="error-box">
            <strong>Import មានជួរមួយចំនួនមិនបានបញ្ចូល</strong>
            <ul>
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="table-panel">
        <form class="toolbar" method="GET">
            <div class="filters">
                <div class="field">
                    <label>ស្វែងរក</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="លេខសម្គាល់ ឈ្មោះ ឬទូរស័ព្ទ">
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
                    <th>លេខសម្គាល់</th>
                    <th>ឈ្មោះ</th>
                    <th>ថ្នាក់</th>
                    <th>អាណាព្យាបាល</th>
                    <th>ទូរស័ព្ទ</th>
                    <th>ស្ថានភាព</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>{{ $student->student_code }}</td>
                        <td><strong>{{ $student->full_name }}</strong><br><span class="muted">{{ $student->gender }}</span></td>
                        <td>{{ $student->classRoom?->name ?? '-' }}</td>
                        <td>{{ $student->parent_name ?? '-' }}</td>
                        <td>{{ $student->phone ?? $student->parent_phone ?? '-' }}</td>
                        <td><span class="badge {{ $student->status === 'active' ? 'success' : 'warning' }}">{{ $student->status }}</span></td>
                        <td>
                            <div class="actions-row">
                                <a class="icon-btn" href="{{ route('students.show', $student) }}" title="មើល"><i data-lucide="eye"></i></a>
                                @if (auth()->user()->role === 'admin')
                                    <a class="icon-btn" href="{{ route('students.edit', $student) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                    <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('តើអ្នកចង់លុបសិស្សនេះមែនទេ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn danger" type="submit" title="លុប"><i data-lucide="trash-2"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">មិនទាន់មានទិន្នន័យសិស្ស</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $students->links() }}</div>
    </section>
@endsection
