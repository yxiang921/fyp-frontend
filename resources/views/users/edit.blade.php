@extends('layouts.app')
@section('title', 'Edit User')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('users.index') }}">User Management</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Edit</span>
</nav>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Edit User</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-input" value="{{ $user->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" value="{{ $user->email }}" required>
                    </div>
                    <div class="form-group">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select" required>
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" {{ $user->role === $role ? 'selected' : '' }}>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="agency" class="form-label">Agency</label>
                        <input type="text" id="agency" name="agency" class="form-input" value="{{ $user->agency }}">
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-input" value="{{ $user->phone }}">
                    </div>
                    <div class="form-group">
                        <label for="is_active" class="form-label">Status</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
