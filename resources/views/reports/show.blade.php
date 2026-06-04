@extends('layouts.app')

@section('title', $title)
@section('subtitle', 'លទ្ធផលរបាយការណ៍ និងជម្រើសនាំចេញ')

@section('actions')
    <a class="btn secondary" href="{{ route('reports.export', [$type, 'excel']) }}"><i data-lucide="download"></i> Excel</a>
    <a class="btn secondary" href="{{ route('reports.export', [$type, 'pdf']) }}" target="_blank"><i data-lucide="printer"></i> PDF</a>
@endsection

@section('content')
    <section class="table-panel">
        <div class="toolbar">
            <strong>{{ $title }}</strong>
            <a class="btn secondary" href="{{ route('reports.index') }}">របាយការណ៍ផ្សេងទៀត</a>
        </div>
        <table>
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell ?: '-' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($headers) }}" class="empty">មិនមានទិន្នន័យសម្រាប់របាយការណ៍នេះ</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
