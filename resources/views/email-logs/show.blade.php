@extends('layouts.app')
@section('title', 'Email Log Detail')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('email-logs.index') }}">Email Logs</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>{{ $emailLog->email_log_id }}</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Email Log: {{ $emailLog->email_log_id }}</h1>
        <p class="text-sm text-gray-500 mt-1">Detailed record of email notification sent by the system.</p>
    </div>
    <a href="{{ route('email-logs.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Logs
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Email details --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Email Details</h2>
                <span class="badge {{ $emailLog->statusBadge() }}">{{ $emailLog->statusLabel() }}</span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Log ID</span>
                        <span class="detail-value font-mono">{{ $emailLog->email_log_id }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><span class="badge {{ $emailLog->statusBadge() }}">{{ $emailLog->statusLabel() }}</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Mailable Type</span>
                        <span class="detail-value"><span class="badge badge-blue">{{ $emailLog->mailableLabel() }}</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Trigger Type</span>
                        <span class="detail-value"><span class="badge {{ $emailLog->trigger_type == 'auto' ? 'badge-purple' : 'badge-teal' }}">{{ $emailLog->triggerTypeLabel() }}</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Triggered By</span>
                        <span class="detail-value">{{ $emailLog->triggeredBy->name ?? 'System' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Sent At</span>
                        <span class="detail-value font-mono">{{ $emailLog->sent_at ? $emailLog->sent_at->format('d M Y, h:i:s A') : '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Created</span>
                        <span class="detail-value font-mono">{{ $emailLog->created_at->format('d M Y, h:i:s A') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Last Updated</span>
                        <span class="detail-value font-mono">{{ $emailLog->updated_at->format('d M Y, h:i:s A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recipient information --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Recipient Information</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Recipient Name</span>
                        <span class="detail-value">{{ $emailLog->recipient_name ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Recipient Email</span>
                        <span class="detail-value font-mono">{{ $emailLog->recipient_email }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Email content info --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Email Content</h2>
            </div>
            <div class="card-body">
                <div class="detail-item mb-4">
                    <span class="detail-label">Subject</span>
                    <span class="detail-value text-sm">{{ $emailLog->subject }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Mailable Class</span>
                    <span class="detail-value font-mono text-xs text-gray-500">{{ $emailLog->mailable_class }}</span>
                </div>
            </div>
        </div>

        {{-- Error information (if failed) --}}
        @if($emailLog->isFailed() && $emailLog->error_message)
        <div class="card border-red-200">
            <div class="card-header bg-red-50">
                <h2 class="text-sm font-semibold text-red-800"><i class="fa-solid fa-triangle-exclamation"></i> Error Information</h2>
            </div>
            <div class="card-body">
                <pre class="text-sm text-red-700 whitespace-pre-wrap font-mono bg-red-50 p-3 rounded">{{ $emailLog->error_message }}</pre>
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        {{-- Related Alert --}}
        @if($emailLog->alert)
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Related Alert</h2>
            </div>
            <div class="card-body space-y-2">
                <div class="detail-item">
                    <span class="detail-label">Alert ID</span>
                    <span class="detail-value">
                        <a href="{{ route('alerts.show', $emailLog->alert) }}" class="text-navy hover:underline font-mono text-xs">
                            {{ $emailLog->alert->alert_id }}
                        </a>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Subject</span>
                    <span class="detail-value text-sm">{{ $emailLog->alert->referenceName() }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value"><span class="badge {{ $emailLog->alert->status->badge() }}">{{ $emailLog->alert->status->label() }}</span></span>
                </div>

                <a href="{{ route('alerts.show', $emailLog->alert) }}" class="btn btn-primary w-full mt-2">
                    <i class="fa-solid fa-bell"></i> View Alert
                </a>
            </div>
        </div>
        @endif

        {{-- Audit note --}}
        <div class="disclaimer">
            <i class="fa-solid fa-info-circle mt-0.5 flex-shrink-0"></i>
            <p>This log entry records that an email was queued for delivery. The actual delivery status depends on the mail server. A "Sent" status means the email was accepted by the SMTP server.</p>
        </div>
    </div>
</div>
@endsection
