@extends('layouts.app')
@section('title', 'Review Verification')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('verifications.index') }}">Verifications</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('verifications.show', $verification) }}">{{ $verification->request_id }}</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Review</span></nav>
@endsection

@section('content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Review Verification: {{ $verification->request_id }}</h1>
    <div class="disclaimer mb-6"><i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i><div><p class="font-medium">PDRM Verification Guidelines</p><p class="mt-0.5">Verify the subject's identity against official PDRM records. Check national ID, name, date of birth, and other identifying information. Your verification confirms or rejects the potential match.</p></div></div>

    <div class="card mb-6"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Subject Information from Police Request</h2></div>
    <div class="card-body"><div class="detail-grid">
        <div class="detail-item"><span class="detail-label">Full Name</span><span class="detail-value font-medium">{{ $verification->detection?->referenceName() ?? 'N/A' }}</span></div>
        <div class="detail-item"><span class="detail-label">Similarity Score</span><span class="detail-value font-bold {{ ($verification->detection?->similarity_score ?? 0) >= 80 ? 'text-red-600' : 'text-gray-600' }}">{{ number_format($verification->detection?->similarity_score ?? 0, 1) }}%</span></div>
        <div class="detail-item"><span class="detail-label">Detection ID</span><span class="detail-value font-mono text-xs">{{ $verification->detection->detection_id ?? 'N/A' }}</span></div>
        <div class="detail-item"><span class="detail-label">Alert ID</span><span class="detail-value font-mono text-xs">{{ $verification->alert->alert_id ?? 'N/A' }}</span></div>
        <div class="detail-item"><span class="detail-label">Requested By</span><span class="detail-value">{{ $verification->requester->name ?? 'N/A' }}</span></div>
    </div></div></div>

    <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Verification Decision</h2></div>
    <div class="card-body">
        <form method="POST" action="{{ route('verifications.review.submit', $verification) }}">
            @csrf
            <div class="form-group">
                <label for="decision" class="form-label">Decision <span class="text-red-500">*</span></label>
                <select id="decision" name="decision" class="form-select @error('decision') border-red-500 @enderror" required>
                    <option value="">Select Decision</option>
                    <option value="confirmed" {{ old('decision') == 'confirmed' ? 'selected' : '' }}>Confirmed — Identity matches PDRM records</option>
                    <option value="rejected" {{ old('decision') == 'rejected' ? 'selected' : '' }}>Rejected — Identity does NOT match PDRM records</option>
                    <option value="insufficient_data" {{ old('decision') == 'insufficient_data' ? 'selected' : '' }}>Insufficient Data — More information required</option>
                </select>
                @error('decision')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label for="jpn_notes" class="form-label">PDRM Review Notes <span class="text-red-500">*</span></label>
                <textarea id="jpn_notes" name="jpn_notes" rows="5" class="form-textarea @error('jpn_notes') border-red-500 @enderror" placeholder="Document your findings, PDRM record comparison results, and rationale for decision..." required>{{ old('jpn_notes') }}</textarea>
                @error('jpn_notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check-double"></i> Submit Verification</button>
                <a href="{{ route('verifications.show', $verification) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>
@endsection
