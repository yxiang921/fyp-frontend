@extends('layouts.app')
@section('title', 'User Management')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>User Management</span>
</nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">User Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage system users and their roles.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add User
    </a>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Agency</th>
                <th>Status</th>
                <th>Created</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->avatarUrl() }}" class="w-8 h-8 rounded-full">
                        <span class="font-medium">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="text-gray-500">{{ $user->email }}</td>
                <td><span class="badge {{ $user->role->badge() }}">{{ $user->role->label() }}</span></td>
                <td class="text-gray-500">{{ $user->agency ?? '—' }}</td>
                <td>
                    <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-gray-500 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-secondary" title="View">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-secondary" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-users text-2xl mb-2 block"></i>
                    No users found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users</p>
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
