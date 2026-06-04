@extends('layouts.app')

@section('title', 'Import គ្រូ')
@section('subtitle', 'បញ្ចូលព័ត៌មានគ្រូច្រើននាក់ក្នុងពេលតែមួយតាម CSV')

@section('actions')
    <a class="btn secondary" href="{{ route('teachers.index') }}"><i data-lucide="arrow-left"></i> ត្រឡប់ក្រោយ</a>
@endsection

@section('content')
    <div class="import-layout">
        <form class="content-band" method="POST" action="{{ route('teachers.import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label>CSV file</label>
                <input type="file" name="csv_file" accept=".csv,text/csv,text/plain" required>
            </div>
            <p class="muted">ប្រព័ន្ធនឹងបង្កើតគ្រូថ្មី ឬកែប្រែគ្រូដែលមានលេខសម្គាល់ដូចគ្នា។</p>
            <div class="actions-row" style="margin-top:18px;">
                <button class="btn" type="submit"><i data-lucide="upload"></i> Import គ្រូ</button>
                <a class="btn secondary" href="{{ route('teachers.index') }}">បោះបង់</a>
            </div>
        </form>

        <section class="content-band">
            <h3>ទម្រង់ CSV</h3>
            <p class="muted">Columns ចាំបាច់មាន: `teacher_code`, `first_name`, `last_name`។ Columns ផ្សេងៗអាចទុកទទេបាន។</p>
            <pre class="csv-example">teacher_code,first_name,last_name,gender,phone,email,address,subject_specialty,hire_date,status,user_email
T-1001,Sophea,Teacher,Male,012345678,sophea@example.com,Phnom Penh,Information Technology,2026-06-01,active,teacher@example.com
T-1002,Sovan,Teacher,Female,011222333,sovan@example.com,Kandal,Mathematics,2026-06-01,active,</pre>
            <div class="import-notes">
                <p><strong>user_email</strong> ប្រើសម្រាប់ភ្ជាប់ទៅគណនី teacher ដែលមានស្រាប់។</p>
                <p><strong>status</strong> អាចជា active ឬ inactive។</p>
                <p>បើ `teacher_code` មានរួចហើយ ប្រព័ន្ធនឹងកែប្រែ record នោះ។</p>
            </div>
        </section>
    </div>
@endsection
