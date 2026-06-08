<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Audit Log - SIOPAL</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #00685f; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #00685f; }
        .header p { margin: 5px 0 0; color: #666; }
        table { w-full; border-collapse: collapse; margin-top: 15px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; color: #555; }
        .status-success { color: green; font-weight: bold; }
        .status-failed { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Log Audit Sistem (SIOPAL)</h2>
        <p>Filter Peran: {{ $role ? $role : 'Semua Peran' }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Pengguna</th>
                <th>Peran</th>
                <th>Aktivitas</th>
                <th>Alamat IP</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</td>
                    <td>{{ $log->pengguna->nama_lengkap ?? 'Sistem' }}</td>
                    <td>{{ $log->pengguna->peran ?? '-' }}</td>
                    <td>{{ $log->aktivitas }}</td>
                    <td>{{ $log->alamat_ip ?? '-' }}</td>
                    <td class="{{ $log->status == 'Success' ? 'status-success' : 'status-failed' }}">
                        {{ $log->status }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Belum ada riwayat aktivitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
