@extends('adminlte::page')

@section('title', 'Schools — Super Admin')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0"><i class="fas fa-building mr-2"></i>All Schools</h1>
        <div>
            <a href="{{ route('super.accounts.index') }}" class="btn btn-outline-secondary mr-1">
                <i class="fas fa-users-cog mr-1"></i> All Accounts
            </a>
            <a href="{{ route('super.schools.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Add School
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

    <div class="card card-outline card-primary">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>#</th>
                        <th>School</th>
                        <th>Slug</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th class="text-center">Users</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Staff</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schools as $school)
                        <tr>
                            <td class="text-muted">{{ $school->id }}</td>
                            <td>
                                <strong>{{ $school->name }}</strong>
                                @if($school->email)
                                    <br><small class="text-muted">{{ $school->email }}</small>
                                @endif
                            </td>
                            <td><code>{{ $school->slug }}</code></td>
                            <td>
                                <span class="badge badge-{{ $school->plan === 'pro' ? 'info' : 'secondary' }}">
                                    {{ strtoupper($school->plan) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $badge = match($school->subscription_status) {
                                        'active'    => 'success',
                                        'trial'     => 'warning',
                                        'expired'   => 'danger',
                                        'cancelled' => 'secondary',
                                        default     => 'dark',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ ucfirst($school->subscription_status) }}</span>
                            </td>
                            <td>
                                {{ $school->subscription_expires_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="text-center">{{ $school->users_count }}</td>
                            <td class="text-center">{{ $school->students_count }}</td>
                            <td class="text-center">{{ $school->staff_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('super.schools.show', $school->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super.schools.edit', $school->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No schools yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schools->hasPages())
            <div class="card-footer">
                {{ $schools->links() }}
            </div>
        @endif
    </div>
@endsection
