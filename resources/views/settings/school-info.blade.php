@extends('adminlte::page')

@section('title', 'School Info')

@section('content')
<div class="card">
    <div class="card-header">School Information</div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('settings.school.info.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $school->name) }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Motto</label>
                <input type="text" name="motto" value="{{ old('motto', $school->motto) }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $school->email) }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="{{ old('address', $school->address) }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Website</label>
                <input type="text" name="website" value="{{ old('website', $school->website) }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Logo</label>
                <input type="file" name="logo" class="form-control">
                @if($school->logo)
                    <img src="{{ asset('storage/'.$school->logo) }}" height="50" class="mt-2">
                @endif
            </div>

            {{-- Guardian Results Lock Settings --}}
            <hr>
            <h5 class="text-primary"><i class="fas fa-lock"></i> Guardian Results Lock</h5>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="lock_results_for_guardians" value="0">
                    <input type="checkbox" class="custom-control-input" id="lock_results" 
                           name="lock_results_for_guardians" value="1" 
                           {{ $school->lock_results_for_guardians ? 'checked' : '' }}>
                    <label class="custom-control-label" for="lock_results">
                        Enable results lock for guardians with unpaid fees
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="lock_results_only_overdue" value="0">
                    <input type="checkbox" class="custom-control-input" id="lock_overdue" 
                           name="lock_results_only_overdue" value="1" 
                           {{ $school->lock_results_only_overdue ? 'checked' : '' }}>
                    <label class="custom-control-label" for="lock_overdue">
                        Lock only when there are <strong>overdue</strong> bills (past due date)
                    </label>
                </div>
                <small class="form-text text-muted">
                    If disabled, any outstanding balance blocks results.
                    If enabled, only bills that are past their due date will block results.
                </small>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
</div>
@stop