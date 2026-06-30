@extends('adminlte::page')

@section('title', $school->name . ' — Super Admin')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
            @if($school->logo)
                <img src="{{ asset('storage/' . $school->logo) }}" alt="logo"
                     style="height:48px;width:48px;object-fit:contain;border-radius:6px;margin-right:12px;">
            @endif
            <div>
                <h1 class="m-0">{{ $school->name }}</h1>
                <small class="text-muted"><code>{{ $school->slug }}.schoolms.tz</code></small>
            </div>
        </div>
        <div>
            <a href="{{ route('super.schools.edit', $school->id) }}" class="btn btn-warning btn-sm mr-1">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('super.schools.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> All Schools
            </a>
        </div>
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

    {{-- ── Stats Row ── --}}
    <div class="row mb-3">
        <div class="col-sm-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ $school->users_count }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-user-graduate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Students</span>
                    <span class="info-box-number">{{ $school->students_count }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-chalkboard-teacher"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Staff</span>
                    <span class="info-box-number">{{ $school->staff_count }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            @php
                $badge = match($school->subscription_status) {
                    'active'    => 'success',
                    'trial'     => 'warning',
                    'expired'   => 'danger',
                    'cancelled' => 'secondary',
                    default     => 'dark',
                };
            @endphp
            <div class="info-box">
                <span class="info-box-icon bg-{{ $badge }}"><i class="fas fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Subscription</span>
                    <span class="info-box-number" style="font-size:1rem;">
                        {{ ucfirst($school->subscription_status) }} / {{ strtoupper($school->plan) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Subscription Management ── --}}
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-credit-card mr-1"></i>Subscription Management</h3>
            <div class="card-tools">
                @php
                    $daysLeft = $school->subscription_expires_at
                        ? now()->diffInDays($school->subscription_expires_at, false)
                        : null;
                @endphp
                @if($daysLeft !== null)
                    <span class="badge badge-{{ $daysLeft > 30 ? 'success' : ($daysLeft > 0 ? 'warning' : 'danger') }} mr-2">
                        {{ $daysLeft > 0 ? $daysLeft . ' days left' : abs($daysLeft) . ' days overdue' }}
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Quick extend buttons --}}
                <div class="col-md-6">
                    <p class="text-muted mb-2"><strong>Quick Extend</strong> <small>(extends from current expiry or today, whichever is later)</small></p>
                    <form action="{{ route('super.schools.renew', $school->id) }}" method="POST" class="d-inline">
                        @csrf
                        @foreach([1 => '1 Mo', 3 => '3 Mo', 6 => '6 Mo', 12 => '1 Yr', 24 => '2 Yr'] as $months => $label)
                            <button type="submit" name="months" value="{{ $months }}"
                                    class="btn btn-sm btn-outline-success mr-1 mb-1">
                                +{{ $label }}
                            </button>
                        @endforeach
                    </form>
                </div>

                {{-- Set to specific date --}}
                <div class="col-md-3">
                    <p class="text-muted mb-2"><strong>Set Expiry Date</strong></p>
                    <form action="{{ route('super.schools.renew', $school->id) }}" method="POST" class="d-flex">
                        @csrf
                        <input type="date" name="custom_date" class="form-control form-control-sm mr-1"
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               value="{{ $school->subscription_expires_at?->format('Y-m-d') }}" required>
                        <button type="submit" class="btn btn-sm btn-success text-nowrap">Set</button>
                    </form>
                </div>

                {{-- Status quick-set --}}
                <div class="col-md-3">
                    <p class="text-muted mb-2"><strong>Set Status</strong></p>
                    <form action="{{ route('super.schools.set-status', $school->id) }}" method="POST">
                        @csrf
                        <div class="d-flex">
                            <select name="status" class="form-control form-control-sm mr-1">
                                @foreach(['active','trial','expired','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $school->subscription_status === $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-warning text-nowrap">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ── School Details ── --}}
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Details</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr><th width="140">Name</th><td>{{ $school->name }}</td></tr>
                        <tr><th>Slug</th><td><code>{{ $school->slug }}</code></td></tr>
                        <tr><th>Email</th><td>{{ $school->email ?? '—' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $school->phone ?? '—' }}</td></tr>
                        <tr><th>Address</th><td>{{ $school->address ?? '—' }}</td></tr>
                        <tr><th>Motto</th><td>{{ $school->motto ?? '—' }}</td></tr>
                        <tr><th>Website</th><td>
                            @if($school->website)
                                <a href="{{ $school->website }}" target="_blank">{{ $school->website }}</a>
                            @else —
                            @endif
                        </td></tr>
                        <tr><th>Plan</th><td><span class="badge badge-info">{{ strtoupper($school->plan) }}</span></td></tr>
                        <tr><th>Sub. Status</th><td><span class="badge badge-{{ $badge }}">{{ ucfirst($school->subscription_status) }}</span></td></tr>
                        <tr><th>Expires At</th><td>{{ $school->subscription_expires_at?->format('d M Y') ?? 'No expiry set' }}</td></tr>
                        <tr><th>Created</th><td>{{ $school->created_at->format('d M Y') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Admins + Add User ── --}}
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i>Admin Users</h3>
                </div>
                <div class="card-body p-0">
                    @if($admins->isEmpty())
                        <p class="text-muted p-3">No admin users found.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th></th></tr></thead>
                            <tbody>
                                @foreach($admins as $admin)
                                    <tr>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>{{ $admin->created_at->format('d M Y') }}</td>
                                        <td>
                                            <button class="btn btn-xs btn-outline-warning"
                                                    data-toggle="collapse"
                                                    data-target="#resetPw{{ $admin->id }}">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="resetPw{{ $admin->id }}">
                                        <td colspan="4" class="bg-light">
                                            <form action="{{ route('super.schools.reset-password', [$school->id, $admin->id]) }}" method="POST" class="d-flex align-items-center gap-2 p-1">
                                                @csrf
                                                <input type="password" name="password" class="form-control form-control-sm mr-1"
                                                       placeholder="New password (min 8)" required minlength="8" style="max-width:200px">
                                                <input type="password" name="password_confirmation" class="form-control form-control-sm mr-1"
                                                       placeholder="Confirm" required style="max-width:160px">
                                                <button type="submit" class="btn btn-sm btn-warning text-nowrap">
                                                    <i class="fas fa-save mr-1"></i>Reset
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-outline-success" data-toggle="collapse" data-target="#addUserForm">
                        <i class="fas fa-plus mr-1"></i> Add User
                    </button>
                </div>
            </div>

            {{-- Add User Form --}}
            <div class="collapse" id="addUserForm">
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">Add User to this School</h3></div>
                    <div class="card-body">
                        <form action="{{ route('super.schools.add-user', $school->id) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Confirm</label>
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role" class="form-control">
                                    <option value="Admin">Admin</option>
                                    <option value="Teacher">Teacher</option>
                                    <option value="Accountant">Accountant</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Add User</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Danger Zone ── --}}
            <div class="card card-outline card-danger">
                <div class="card-header"><h3 class="card-title text-danger">Danger Zone</h3></div>
                <div class="card-body">
                    <form action="{{ route('super.schools.destroy', $school->id) }}" method="POST"
                          onsubmit="return confirm('Delete {{ $school->name }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash mr-1"></i> Delete School
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
