@extends('layouts.app')
@section('title', 'Create User')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('users.index') }}">User Management</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Create User</span>
</nav>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Create User</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="super_admin">Super Admin (PDRM IT Department)</option>
                            <option value="pdrm_officer">PDRM Officer</option>
                            <option value="patrol_unit">Patrol Unit</option>
                            <option value="public_user">Public User</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="agency" class="form-label">Agency</label>
                        <input type="text" id="agency" name="agency" class="form-input" placeholder="e.g. IPD Kuala Lumpur">
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-input" placeholder="+60">
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    <div class="form-group md:col-span-2" id="patrol-area-fields">
                        <label class="form-label">Patrol areas (required for patrol units)</label>
                        <div class="grid grid-cols-2 gap-2">
                        @foreach($patrolAreas as $area)<label><input type="checkbox" name="patrol_area_ids[]" value="{{ $area->id }}" @checked(in_array($area->id, old('patrol_area_ids', [])))> {{ $area->name }}</label>@endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Create User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
