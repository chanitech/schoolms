@extends('adminlte::page')

@section('title', 'Roles & Permissions')

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Roles & Permissions Management</h1>
@stop

@section('content')
<div class="row">
    {{-- Assign Role to User --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Assign Role to User</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('roles.assign') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="user_id">Select User</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">-- Choose User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-2">
                        <label for="role">Select Role</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">-- Choose Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success mt-3 w-100">
                        <i class="fas fa-check-circle"></i> Assign Role
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Manage Role Permissions --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="fas fa-lock"></i> Manage Role Permissions</h4>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="roleTabs" role="tablist">
                    @foreach($roles as $index => $role)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="tab-{{ $role->id }}" data-bs-toggle="tab" data-bs-target="#role-{{ $role->id }}" type="button" role="tab">
                                {{ ucfirst($role->name) }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content mt-3">
                    @foreach($roles as $index => $role)
                        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="role-{{ $role->id }}" role="tabpanel">
                            <form action="{{ route('roles.permissions.update', $role->id) }}" method="POST">
                                @csrf
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                    id="perm-{{ $role->id }}-{{ $permission->id }}"
                                                    {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm-{{ $role->id }}-{{ $permission->id }}">
                                                    {{ ucfirst($permission->name) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="fas fa-save"></i> Update Permissions
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- You can add custom styling here if needed --}}
@stop

@section('js')
    <script>
        console.log("Roles & Permissions Management loaded successfully!");
    </script>
@stop
