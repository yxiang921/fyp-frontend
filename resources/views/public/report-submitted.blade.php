@extends('layouts.guest')
@section('title', 'Report Submitted')

@section('auth-content')
<div class="text-center">
    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid fa-circle-check text-green-600 text-3xl"></i>
    </div>

    <h2 class="text-lg font-semibold text-gray-900">Report Submitted Successfully</h2>
    <p class="text-sm text-gray-500 mt-2">Your missing person report has been received and will be reviewed by a PDRM officer.</p>

    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
        <p class="text-xs text-gray-500 mb-1">Your Case Reference Number</p>
        <p class="text-xl font-mono font-bold text-navy">{{ $caseNumber }}</p>
    </div>

    <div class="mt-6 text-sm text-gray-600 text-left space-y-2">
        <p><i class="fa-solid fa-clipboard-list text-navy mr-2"></i> <strong>Save this case number</strong> — you will need it if you contact PDRM about this case.</p>
        <p><i class="fa-solid fa-clock text-navy mr-2"></i> A PDRM officer will review your submission and verify the information provided.</p>
        <p><i class="fa-solid fa-camera text-navy mr-2"></i> Once verified, facial recognition monitoring will begin to help locate the missing person.</p>
    </div>

    <div class="mt-6 pt-4 border-t border-gray-200">
        <a href="{{ route('public.report-missing') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Submit Another Report
        </a>
    </div>
</div>
@endsection
