@extends('adminlte::page')

@section('title', 'Edit ' . $school->name . ' — Super Admin')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1><i class="fas fa-edit mr-2"></i>Edit: {{ $school->name }}</h1>
        <a href="{{ route('super.schools.show', $school->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')
<form action="{{ route('super.schools.update', $school->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        {{-- ===== School Details ===== --}}
        <div class="col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">School Information</h3></div>
                <div class="card-body">

                    <div class="form-group">
                        <label>School Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $school->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>School Code (used to log in, also used in subdomain) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $school->slug) }}" required>
                            <div class="input-group-append">
                                <span class="input-group-text">.{{ config('tenancy.domain', 'schoolms.ac.tz') }}</span>
                            </div>
                        </div>
                        <small class="text-muted">Changing this will break existing subdomain links.</small>
                        @error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $school->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone', $school->phone) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $school->address) }}">
                    </div>

                    <div class="form-group">
                        <label>Motto</label>
                        <input type="text" name="motto" class="form-control"
                               value="{{ old('motto', $school->motto) }}">
                    </div>

                    <div class="form-group">
                        <label>Website</label>
                        <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                               value="{{ old('website', $school->website) }}" placeholder="https://...">
                        @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Logo</label>
                        @if($school->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $school->logo) }}" alt="Current logo"
                                     style="height:60px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;padding:4px;">
                                <small class="d-block text-muted">Upload a new file to replace.</small>
                            </div>
                        @endif
                        <div class="custom-file">
                            <input type="file" name="logo" class="custom-file-input @error('logo') is-invalid @enderror"
                                   id="logoFile" accept="image/*">
                            <label class="custom-file-label" for="logoFile">Choose file...</label>
                        </div>
                        @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ===== Subscription ===== --}}
        <div class="col-lg-5">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-credit-card mr-1"></i>Subscription</h3></div>
                <div class="card-body">

                    <div class="form-group">
                        <label>Plan <span class="text-danger">*</span></label>
                        <select name="plan" class="form-control">
                            <option value="pro"   {{ old('plan', $school->plan) === 'pro'   ? 'selected' : '' }}>Pro</option>
                            <option value="basic" {{ old('plan', $school->plan) === 'basic' ? 'selected' : '' }}>Basic</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="subscription_status" class="form-control">
                            @foreach(['active','trial','expired','cancelled'] as $status)
                                <option value="{{ $status }}"
                                    {{ old('subscription_status', $school->subscription_status) === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subscription Expires At</label>
                        <input type="date" name="subscription_expires_at" class="form-control"
                               value="{{ old('subscription_expires_at', $school->subscription_expires_at?->format('Y-m-d')) }}">
                        <small class="text-muted">Leave blank for no expiry.</small>
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                    <a href="{{ route('super.schools.show', $school->id) }}" class="btn btn-secondary btn-block">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script>
document.getElementById('logoFile').addEventListener('change', function () {
    const label = this.nextElementSibling;
    label.textContent = this.files[0]?.name ?? 'Choose file...';
});
</script>
@endsection
