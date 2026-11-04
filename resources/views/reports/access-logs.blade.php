@extends('layouts.app')
@section('title', 'Privacy access logs')
@section('content')
<h1 class="text-xl font-semibold mb-4">Privacy access logs</h1>
<p class="mb-4">Case, witness and evidence access. Network addresses are recorded as keyed hashes. Retention: {{ config('reporting.access_log_retention_days') }} days.</p>
<table class="data-table"><thead><tr><th>Time</th><th>User ID</th><th>Action</th><th>Resource</th><th>Resource ID</th></tr></thead><tbody>
@foreach($logs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->user_id ?? 'Guest / system' }}</td><td>{{ $log->action }}</td><td>{{ $log->resource_type }}</td><td>{{ $log->resource_id }}</td></tr>@endforeach
</tbody></table>{{ $logs->links() }}
@endsection
