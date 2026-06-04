@extends('layouts.app')

@section('title', 'របាយការណ៍')
@section('subtitle', 'បង្កើតរបាយការណ៍ប្រចាំថ្ងៃ ខែ ឆ្នាំ និងនាំចេញជា Excel/PDF')

@section('content')
    <div class="grid grid-3">
        @foreach ($reports as $type => $label)
            <section class="content-band">
                <h3>{{ $label }}</h3>
                <p class="muted">មើលទិន្នន័យ នាំចេញ CSV ដែល Excel អាចបើកបាន និងទំព័របោះពុម្ព/PDF។</p>
                <div class="actions-row">
                    <a class="btn" href="{{ route('reports.show', $type) }}"><i data-lucide="file-search"></i> មើល</a>
                    <a class="btn secondary" href="{{ route('reports.export', [$type, 'excel']) }}"><i data-lucide="download"></i> Excel</a>
                    <a class="btn secondary" href="{{ route('reports.export', [$type, 'pdf']) }}" target="_blank"><i data-lucide="printer"></i> PDF</a>
                </div>
            </section>
        @endforeach
    </div>
@endsection
