@extends('adminlte::page')

@section('title', 'Biometric Devices')

@section('content_header')
    <h1>Biometric Devices</h1>
@stop

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Relay setup card --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-fingerprint"></i> Fingerprint Relay Setup</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Give these values to the relay script running on a PC at the school
                (see <code>tools/zkteco-relay/README.md</code>). The device key below
                is unique to this school — keep it private.
            </p>

            <div class="form-group">
                <label>School Slug</label>
                <input type="text" class="form-control" value="{{ $school->slug }}" readonly>
            </div>

            <div class="form-group">
                <label>Endpoint URL</label>
                <input type="text" class="form-control" value="{{ $endpointUrl }}" readonly>
            </div>

            <div class="form-group">
                <label>Device Key (X-Device-Key header)</label>
                @if($school->biometric_api_key)
                    <input type="text" class="form-control" value="{{ $school->biometric_api_key }}" readonly>
                @else
                    <input type="text" class="form-control" value="Not generated yet" readonly disabled>
                @endif
            </div>

            <form action="{{ route('settings.biometric-devices.regenerate-key') }}" method="POST"
                  onsubmit="return confirm('Regenerating invalidates the current key immediately — the relay script will need the new one to keep working. Continue?')">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sync-alt"></i> {{ $school->biometric_api_key ? 'Regenerate Key' : 'Generate Key' }}
                </button>
            </form>
        </div>
    </div>

    {{-- Unmatched scans card --}}
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Unmatched Scans</h3>
        </div>
        <div class="card-body table-responsive">
            @if($unmatchedDeviceIds->count())
                <p class="text-muted">
                    These device IDs scanned in but aren't linked to a staff member yet.
                    Map each one to backfill its attendance history.
                </p>
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Device ID</th>
                            <th>Map to Staff</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unmatchedDeviceIds as $i => $deviceId)
                            {{-- A <form> can't legally wrap a <tr> (browsers hoist it out of
                                 the table, breaking submission), so the form only holds the
                                 hidden/CSRF fields and the select+button reference it by id
                                 via the HTML5 `form` attribute instead of DOM nesting. --}}
                            <tr>
                                <td class="align-middle">
                                    <code>{{ $deviceId }}</code>
                                    <form id="map-form-{{ $i }}" action="{{ route('settings.biometric-devices.map-unmatched') }}" method="POST" class="d-none">
                                        @csrf
                                        <input type="hidden" name="device_user_id" value="{{ $deviceId }}">
                                    </form>
                                </td>
                                <td>
                                    <select name="staff_id" form="map-form-{{ $i }}" class="form-control" required>
                                        <option value="">-- Select Staff --</option>
                                        @foreach($staff as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <button type="submit" form="map-form-{{ $i }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-link"></i> Map
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-muted mb-0">No unmatched scans.</p>
            @endif
        </div>
    </div>

    {{-- Recent scans card --}}
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Recent Scans</h3>
        </div>
        <div class="card-body table-responsive">
            @if($recentScans->count())
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Device ID</th>
                            <th>Scanned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentScans as $scan)
                            <tr>
                                <td>
                                    @if($scan->staff)
                                        {{ $scan->staff->name }}
                                    @else
                                        <span class="badge bg-warning text-dark">Unmatched</span>
                                    @endif
                                </td>
                                <td><code>{{ $scan->device_user_id }}</code></td>
                                <td>{{ $scan->scanned_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-muted mb-0">No scans received yet.</p>
            @endif
        </div>
    </div>

</div>
@stop
