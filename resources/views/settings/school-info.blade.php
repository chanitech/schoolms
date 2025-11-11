@extends('adminlte::page')

@section('title', 'School Info')

@section('content')
<div class="card">
    <div class="card-header">School Information</div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('school.info.update') }}" method="POST" enctype="multipart/form-data">
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
                <label>Logo</label>
                <input type="file" name="logo" class="form-control">
                @if($school->logo)
                    <img src="{{ asset('storage/'.$school->logo) }}" height="50" class="mt-2">
                @endif
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
