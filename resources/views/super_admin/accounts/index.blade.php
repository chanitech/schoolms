@extends('adminlte::page')

@section('title', 'All Accounts — Super Admin')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0"><i class="fas fa-users-cog mr-2"></i>All Accounts</h1>
        <a href="{{ route('super.schools.index') }}" class="btn btn-secondary">
            <i class="fas fa-building mr-1"></i> All Schools
        </a>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('super.accounts.index') }}" class="row align-items-end">
                <div class="col-md-4 form-group mb-2">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, email, or phone"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3 form-group mb-2">
                    <label>School</label>
                    <select name="school_id" class="form-control">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ (string) request('school_id') === (string) $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-2">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                </div>
            </form>
            @if(request()->hasAny(['search', 'school_id', 'role']))
                <a href="{{ route('super.accounts.index') }}" class="small">
                    <i class="fas fa-times mr-1"></i>Clear filters
                </a>
            @endif
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>School</th>
                        <th>Roles</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>
                                @if($user->school)
                                    <a href="{{ route('super.schools.show', $user->school->id) }}">
                                        <span class="badge badge-info">{{ $user->school->name }}</span>
                                    </a>
                                @else
                                    <span class="badge badge-secondary">No school</span>
                                @endif
                                @if($user->is_super_admin)
                                    <span class="badge badge-dark">Super Admin</span>
                                @endif
                            </td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge badge-light border">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-right">
                                <button class="btn btn-xs btn-outline-warning" data-toggle="collapse" data-target="#resetPw{{ $user->id }}">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-primary" data-toggle="collapse" data-target="#moveSchool{{ $user->id }}">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                            </td>
                        </tr>
        <tr class="collapse" id="resetPw{{ $user->id }}">
                            <td colspan="7" class="bg-light">
                                @if($user->school_id)
                                    <form action="{{ route('super.schools.reset-password', [$user->school_id, $user->id]) }}" method="POST" class="d-flex align-items-center gap-2 p-1">
                                        @csrf
                                        <input type="password" name="password" class="form-control form-control-sm mr-1"
                                               placeholder="New password (min 8)" required minlength="8" style="max-width:200px">
                                        <input type="password" name="password_confirmation" class="form-control form-control-sm mr-1"
                                               placeholder="Confirm" required style="max-width:160px">
                                        <button type="submit" class="btn btn-sm btn-warning text-nowrap">
                                            <i class="fas fa-save mr-1"></i>Reset Password
                                        </button>
                                    </form>
                                @else
                                    <span class="text-danger small p-1">No school assigned — assign one below before resetting.</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="collapse" id="moveSchool{{ $user->id }}">
                            <td colspan="7" class="bg-light">
                                <form action="{{ route('super.accounts.change-school', $user->id) }}" method="POST" class="d-flex align-items-center gap-2 p-1">
                                    @csrf
                                    <select name="school_id" class="form-control form-control-sm mr-1" style="max-width:260px" required>
                                        <option value="">Move to school…</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" {{ (int) $user->school_id === $school->id ? 'selected' : '' }}>
                                                {{ $school->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                        <i class="fas fa-check mr-1"></i>Move
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
