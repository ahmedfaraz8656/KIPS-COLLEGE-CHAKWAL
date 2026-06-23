<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
    h2 { text-align: center; color: #1E3A5F; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1E3A5F; color: #fff; padding: 5px; font-size: 9px; }
    td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 9px; }
</style>
</head>
<body>
<h2>KIPS College Chakwal — System Audit Trail</h2>
<p style="text-align:center;color:#666;">Generated: {{ now()->format('d M Y, h:i A') }}</p>
<table>
    <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Module</th><th>Description</th></tr></thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->created_at->format('d-M-Y h:i A') }}</td>
            <td>{{ $log->user?->name ?? 'System' }}</td>
            <td>{{ $log->action }}</td>
            <td>{{ $log->module }}</td>
            <td>{{ $log->description }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
