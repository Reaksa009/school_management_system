@extends('layouts.app')

@section('title', 'Import សិស្ស')
@section('subtitle', 'បញ្ចូលសិស្សច្រើននាក់ក្នុងពេលតែមួយតាម CSV')

@section('actions')
    <a class="btn secondary" href="{{ route('students.index') }}"><i data-lucide="arrow-left"></i> ត្រឡប់ក្រោយ</a>
@endsection

@section('content')
    <div class="import-layout">
        <form class="content-band" method="POST" action="{{ route('students.import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label>CSV file</label>
                <input type="file" name="csv_file" accept=".csv,text/csv,text/plain" required>
            </div>
            <p class="muted">ប្រព័ន្ធនឹងបង្កើតសិស្សថ្មី ឬកែប្រែសិស្សដែលមានលេខសម្គាល់ដូចគ្នា។ ថ្នាក់ត្រូវមានជាមុន ប្រសិនបើប្រើ `class_name`។</p>
            <div class="actions-row" style="margin-top:18px;">
                <button class="btn" type="submit"><i data-lucide="upload"></i> Import សិស្ស</button>
                <a class="btn secondary" href="{{ route('students.index') }}">បោះបង់</a>
            </div>
        </form>

        <section class="content-band">
            <h3>ទម្រង់ CSV</h3>
            <p class="muted">Columns ចាំបាច់មាន: `student_code`, `first_name`, `last_name`។ Columns ផ្សេងៗអាចទុកទទេបាន។</p>
            <pre class="csv-example">student_code,first_name,last_name,class_name,gender,date_of_birth,phone,email,address,parent_name,parent_phone,enrollment_date,status,parent_email
S-1001,Dara,Sok,IT,Male,2010-02-15,012345678,dara@example.com,Phnom Penh,Sokha,098765432,2026-06-01,active,parent@example.com
S-1002,Srey,Nita,IT,Female,2011-03-20,,,,Mother Nita,011222333,2026-06-01,active,</pre>
            <div class="import-notes">
                <p><strong>class_name</strong> ត្រូវតែដូចឈ្មោះថ្នាក់ដែលមានក្នុងប្រព័ន្ធ។</p>
                <p><strong>parent_email</strong> ប្រើសម្រាប់ភ្ជាប់ទៅគណនីអាណាព្យាបាលដែលមានស្រាប់។</p>
                <p><strong>status</strong> អាចជា active, inactive, graduated, transferred។</p>
            </div>
        </section>
    </div>
@endsection
