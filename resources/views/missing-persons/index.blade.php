@extends('layouts.app')
@section('title', 'Missing Persons')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Missing Persons</span>
</nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Missing Persons</h1>
        <p class="text-sm text-gray-500 mt-1">Manage missing person cases and records.</p>
    </div>
    <a href="{{ route('missing-persons.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Missing Person
    </a>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<form method="GET" action="{{ route('missing-persons.index') }}" class="filter-bar">
    <div class="flex-1 min-w-[200px]">
        <input type="search" name="search" placeholder="Search name, case number..." class="form-input" value="{{ request('search') }}">
    </div>
    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="found" {{ request('status') == 'found' ? 'selected' : '' }}>Found</option>
        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
        <option value="reopened" {{ request('status') == 'reopened' ? 'selected' : '' }}>Reopened</option>
    </select>
    <select name="gender" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">All Genders</option>
        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
    </select>
    <button type="submit" class="btn btn-secondary">
        <i class="fa-solid fa-magnifying-glass"></i> Filter
    </button>
</form>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Case Number</th>
                <th>Photo</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Last Seen</th>
                <th>Status</th>
                <th>Date Filed</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($missingPersons as $mp)
            <tr>
                <td><code class="text-xs font-mono text-navy">{{ $mp->case_number }}</code></td>
                <td>
                    <div class="w-9 h-9 rounded bg-gray-200 overflow-hidden">
                        @if($mp->primaryPhoto)
                            <img src="{{ $mp->primaryPhoto->url() }}" class="w-full h-full object-cover" alt="{{ $mp->full_name }}">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($mp->full_name) }}&background=1B3A5C&color=fff&size=36" class="w-full h-full object-cover" alt="{{ $mp->full_name }}">
                        @endif
                    </div>
                </td>
                <td><a href="{{ route('missing-persons.show', $mp) }}" class="text-navy hover:underline font-medium">{{ $mp->full_name }}</a></td>
                <td class="text-sm capitalize">{{ $mp->gender }}</td>
                <td class="text-gray-500 text-sm">{{ $mp->last_seen_location }}</td>
                <td><span class="badge {{ $mp->status->badge() }}">{{ $mp->status->label() }}</span></td>
                <td class="text-gray-500 text-xs">{{ $mp->created_at->format('d M Y') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('missing-persons.show', $mp) }}" class="btn btn-sm btn-secondary" title="View"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('missing-persons.edit', $mp) }}" class="btn btn-sm btn-secondary" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                        @if(auth()->user()->isSuperAdmin())
                        <form method="POST" action="{{ route('missing-persons.destroy', $mp) }}" onsubmit="return confirm('Delete case {{ $mp->case_number }}? This cannot be undone.')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                    No missing person records found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($missingPersons->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">
            Showing {{ $missingPersons->firstItem() }} to {{ $missingPersons->lastItem() }} of {{ $missingPersons->total() }} entries
        </p>
        <div class="flex items-center gap-1">
            {{ $missingPersons->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
