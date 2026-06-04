<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('css/sms.css') }}">
</head>
<body>
    <main class="main">
        <div class="print-actions">
            <button class="btn" onclick="window.print()">បោះពុម្ព/PDF</button>
        </div>
        <section class="table-panel">
            <div class="toolbar">
                <div class="report-print-brand">
                    <img class="brand-logo" src="{{ asset(config('school.logo')) }}" alt="{{ config('school.short_name') }} logo">
                    <div>
                        <strong>{{ config('school.name_km') }}</strong>
                        <span>{{ config('school.name_en') }}</span>
                    </div>
                </div>
                <div>
                    <h1>{{ $title }}</h1>
                    <p class="muted">{{ now()->format('Y-m-d H:i') }}</p>
                </div>
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
    </main>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
