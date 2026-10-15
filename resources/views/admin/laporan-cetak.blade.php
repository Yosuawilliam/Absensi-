<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $lap['judul'] }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; color: #1e293b; padding: 20px; }
        h1 { font-size: 16pt; margin-bottom: 4px; }
        p { font-size: 9pt; color: #64748b; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10pt; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; uppercase; }
        .ringkasan { margin-top: 15px; display: flex; gap: 20px; font-size: 10pt; }
        .ringkasan-item { background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 6px; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f172a; pb: 10px;">
        <div>
            <h1>{{ \App\Models\Pengaturan::namaInstitusi() }}</h1>
            <p>{{ $lap['judul'] }}</p>
        </div>
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Cetak Ke PDF</button>
    </div>

    <div class="ringkasan">
        @foreach($lap['ringkasan'] as $k => $v)
            <div class="ringkasan-item">
                <strong>{{ $k }}:</strong> {{ $v }}
            </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                @foreach($lap['kolom'] as $k)
                    <th>{{ $k }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($lap['baris'] as $i => $b)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    @foreach($b as $val)
                        <td>{{ $val }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
