@extends('layouts.app')

@section('title', 'ព័ត៌មានគ្រូ')
@section('subtitle', $teacher->full_name)

@section('actions')
    @if (auth()->user()->role === 'admin')
        <a class="btn" href="{{ route('teachers.edit', $teacher) }}"><i data-lucide="pencil"></i> កែប្រែ</a>
    @endif
@endsection

@section('content')
    <section class="content-band">
        <div class="grid grid-3">
            <p><span class="muted">លេខសម្គាល់</span><br><strong>{{ $teacher->teacher_code }}</strong></p>
            <p><span class="muted">ទូរស័ព្ទ</span><br><strong>{{ $teacher->phone ?? '-' }}</strong></p>
            <p><span class="muted">ឯកទេស</span><br><strong>{{ $teacher->subject_specialty ?? '-' }}</strong></p>
            <p><span class="muted">គណនី</span><br><strong>{{ $teacher->user?->email ?? '-' }}</strong></p>
            <p><span class="muted">ថ្ងៃចូលធ្វើការ</span><br><strong>{{ $teacher->hire_date?->format('Y-m-d') ?? '-' }}</strong></p>
            <p><span class="muted">ស្ថានភាព</span><br><strong>{{ $teacher->status }}</strong></p>
        </div>
    </section>

    <div class="grid grid-2" style="margin-top:16px;">
        <section class="content-band">
            <h3>ថ្នាក់ដែលទទួលខុសត្រូវ</h3>
            @forelse ($teacher->classes as $classRoom)
                <p>{{ $classRoom->name }} · {{ $classRoom->academic_year }}</p>
            @empty
                <p class="muted">មិនទាន់មានថ្នាក់</p>
            @endforelse
        </section>
        <section class="content-band">
            <h3>មុខវិជ្ជា</h3>
            @forelse ($teacher->subjects as $subject)
                <p>{{ $subject->code }} · {{ $subject->name }}</p>
            @empty
                <p class="muted">មិនទាន់មានមុខវិជ្ជា</p>
            @endforelse
        </section>
    </div>
@endsection
