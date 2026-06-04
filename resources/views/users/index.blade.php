@extends('layouts.app')

@section('title', 'គ្រប់គ្រងអ្នកប្រើប្រាស់')
@section('subtitle', 'បង្កើតគណនី និងកំណត់សិទ្ធិតាមតួនាទី')

@section('actions')
    <a class="btn" href="{{ route('users.create') }}"><i data-lucide="plus"></i> បន្ថែមអ្នកប្រើប្រាស់</a>
@endsection

@section('content')
    <section class="table-panel">
        <form class="toolbar" method="GET">
            <div class="filters">
                <div class="field">
                    <label>ស្វែងរក</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="ឈ្មោះ អ៊ីមែល ឬទូរស័ព្ទ">
                </div>
                <div class="field">
                    <label>តួនាទី</label>
                    <select name="role">
                        <option value="">តួនាទីទាំងអស់</option>
                        @foreach (\App\Http\Controllers\UserController::roles() as $value => $label)
                            <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn secondary" type="submit"><i data-lucide="search"></i> ស្វែងរក</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ឈ្មោះ</th>
                    <th>អ៊ីមែល</th>
                    <th>ទូរស័ព្ទ</th>
                    <th>តួនាទី</th>
                    <th>ស្ថានភាព</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>{{ $user->roleLabel() }}</td>
                        <td><span class="badge {{ $user->is_active ? 'success' : 'warning' }}">{{ $user->is_active ? 'កំពុងប្រើ' : 'ផ្អាក' }}</span></td>
                        <td>
                            <div class="actions-row">
                                <a class="icon-btn" href="{{ route('users.edit', $user) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                @if (! $user->is(auth()->user()))
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('តើអ្នកចង់លុបអ្នកប្រើប្រាស់នេះមែនទេ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn danger" type="submit" title="លុប"><i data-lucide="trash-2"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">មិនទាន់មានអ្នកប្រើប្រាស់</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $users->links() }}</div>
    </section>
@endsection
