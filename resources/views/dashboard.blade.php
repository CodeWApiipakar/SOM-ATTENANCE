@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">

    <h2>Biometric Attendance – Recent Punches</h2>
    <details class="mt-3">
        <summary>Devices ({{ $devices->count() }})</summary>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>SN</th>
                    <th>IP</th>
                    <th>Model</th>
                    <th>Active</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devices as $d)
                <tr>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->serial_number }}</td>
                    <td>{{ $d->ip_address }}</td>
                    <td>{{ $d->model }}</td>
                    <td>{{ $d->is_active ? 'Yes' : 'No' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </details>

    <h4 class="mt-4">Latest 25</h4>
    <table id="attendanceTable" class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>Time (UTC)</th>
                <th>Enroll</th>
                <th>Employee</th>
                <th>Device</th>
                <th>Mode</th>
                <th>IO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent as $p)
            <tr>
                <td>{{ $p->punch_time->format('Y-m-d H:i:s') }}</td>
                <td>{{ $p->enroll_id }}</td>
                <td>{{ optional($p->employee)->name ?? '-' }}</td>
                <td>{{ $p->device->name }}</td>
                <td>{{ $p->verify_mode }}</td>
                <td>{{ $p->io_mode }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#attendanceTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
    });
});
</script>
@endpush
