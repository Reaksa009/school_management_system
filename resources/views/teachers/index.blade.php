@extends('layouts.app')

@section('title', 'គ្រប់គ្រងគ្រូ')
@section('subtitle', 'ព័ត៌មានគ្រូ ឯកទេស និងថ្នាក់ដែលទទួលខុសត្រូវ')

@section('actions')
    @if (auth()->user()->role === 'admin')
        <a class="btn secondary" href="{{ route('teachers.import') }}"><i data-lucide="upload"></i> Import CSV</a>
        <a class="btn" href="{{ route('teachers.create') }}"><i data-lucide="plus"></i> បន្ថែមគ្រូ</a>
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
            <div class="field">
                <label>ស្វែងរក</label>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="លេខសម្គាល់ ឈ្មោះ ឬឯកទេស">
            </div>
            <button class="btn secondary" type="submit"><i data-lucide="search"></i> ស្វែងរក</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>លេខសម្គាល់</th>
                    <th>ឈ្មោះ</th>
                    <th>ទូរស័ព្ទ</th>
                    <th>ឯកទេស</th>
                    <th>គណនី</th>
                    <th>ស្ថានភាព</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    <tr>
                        <td>{{ $teacher->teacher_code }}</td>
                        <td><strong>{{ $teacher->full_name }}</strong><br><span class="muted">{{ $teacher->gender }}</span></td>
                        <td>{{ $teacher->phone ?? '-' }}</td>
                        <td>{{ $teacher->subject_specialty ?? '-' }}</td>
                        <td>{{ $teacher->user?->email ?? '-' }}</td>
                        <td><span class="badge {{ $teacher->status === 'active' ? 'success' : 'warning' }}">{{ $teacher->status }}</span></td>
                        <td>
                            <div class="actions-row">
                                <a class="icon-btn" href="{{ route('teachers.show', $teacher) }}" title="មើល"><i data-lucide="eye"></i></a>
                                @if (auth()->user()->role === 'admin')
                                    <a class="icon-btn" href="{{ route('teachers.edit', $teacher) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                    <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" onsubmit="return confirm('តើអ្នកចង់លុបគ្រូនេះមែនទេ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn danger" type="submit" title="លុប"><i data-lucide="trash-2"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">មិនទាន់មានទិន្នន័យគ្រូ</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $teachers->links() }}</div>
    </section>
@endsection
