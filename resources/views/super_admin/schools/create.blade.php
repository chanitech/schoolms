@extends('adminlte::page')

@section('title', 'Add School — Super Admin')

@section('content_header')
    <h1><i class="fas fa-plus-circle mr-2"></i>Add New School</h1>
@endsection

@section('content')
<form action="{{ route('super.schools.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
        {{-- ===== School Details ===== --}}
        <div class="col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">School Information</h3></div>
                <div class="card-body">

                    <div class="form-group">
                        <label>School Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Mema Secondary School" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>School Code (used to log in, also used in subdomain) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="slug" id="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}" placeholder="mema" required>
                            <div class="input-group-append">
                                <span class="input-group-text">.{{ config('tenancy.domain', 'schoolms.ac.tz') }}</span>
                            </div>
                        </div>
                        <small class="text-muted">Lowercase letters, numbers, hyphens only.</small>
                        @error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>

                    <div class="form-group">
                        <label>Motto</label>
                        <input type="text" name="motto" class="form-control" value="{{ old('motto') }}"
                               placeholder="e.g. Knowledge is Power">
                    </div>

                    <div class="form-group">
                        <label>Website</label>
                        <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                               value="{{ old('website') }}" placeholder="https://...">
                        @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>School Logo</label>
                        <div class="custom-file">
                            <input type="file" name="logo" class="custom-file-input @error('logo') is-invalid @enderror"
                                   id="logoFile" accept="image/*">
                            <label class="custom-file-label" for="logoFile">Choose file...</label>
                        </div>
                        @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>

            {{-- ===== First Admin User ===== --}}
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-user-shield mr-1"></i>First Admin User</h3></div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" name="admin_first_name" class="form-control @error('admin_first_name') is-invalid @enderror"
                                       value="{{ old('admin_first_name') }}" required>
                                @error('admin_first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="admin_last_name" class="form-control @error('admin_last_name') is-invalid @enderror"
                                       value="{{ old('admin_last_name') }}" required>
                                @error('admin_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email / Username <span class="text-danger">*</span></label>
                        <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror"
                               value="{{ old('admin_email') }}" required>
                        @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" name="admin_password"
                                       class="form-control @error('admin_password') is-invalid @enderror" required>
                                @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="admin_password_confirmation" class="form-control" required>
                            </div>
                        </div>
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
                            <option value="pro"   {{ old('plan','pro') === 'pro'   ? 'selected' : '' }}>Pro</option>
                            <option value="basic" {{ old('plan') === 'basic' ? 'selected' : '' }}>Basic</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="subscription_status" class="form-control">
                            <option value="active"    {{ old('subscription_status','active') === 'active'    ? 'selected' : '' }}>Active</option>
                            <option value="trial"     {{ old('subscription_status') === 'trial'     ? 'selected' : '' }}>Trial</option>
                            <option value="expired"   {{ old('subscription_status') === 'expired'   ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ old('subscription_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subscription Expires At</label>
                        <input type="date" name="subscription_expires_at" class="form-control"
                               value="{{ old('subscription_expires_at') }}">
                        <small class="text-muted">Leave blank for no expiry.</small>
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save mr-1"></i> Create School
                    </button>
                    <a href="{{ route('super.schools.index') }}" class="btn btn-secondary btn-block">
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
// Auto-generate slug from name
document.querySelector('[name="name"]').addEventListener('input', function () {
    const slug = document.getElementById('slug');
    if (!slug.dataset.edited) {
        slug.value = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
    }
});
document.getElementById('slug').addEventListener('input', function () {
    this.dataset.edited = '1';
});
// Custom file input label
document.getElementById('logoFile').addEventListener('change', function () {
    const label = this.nextElementSibling;
    label.textContent = this.files[0]?.name ?? 'Choose file...';
});
</script>
@endsection
