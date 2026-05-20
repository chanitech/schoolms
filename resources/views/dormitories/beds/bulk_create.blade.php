@extends('adminlte::page')

@section('title', 'Bulk Create Beds')

@section('content_header')
    <h1><i class="fas fa-bed"></i> Bulk Create Beds for Room {{ $room->room_number }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('dormitories.beds.bulk.store', $room->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Starting Bed Number</label>
                        <input type="number" name="start_number" class="form-control" value="1" min="1" required>
                        <small class="text-muted">e.g., 1 will create beds 1,2,3,…</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Number of Beds</label>
                        <input type="number" name="quantity" class="form-control" value="10" min="1" max="200" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bed Type</label>
                        <select name="bed_type" class="form-control">
                            <option value="single">Single</option>
                            <option value="bunk_upper">Bunk Upper</option>
                            <option value="bunk_lower">Bunk Lower</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Beds</button>
            <a href="{{ route('dormitories.beds', $room->id) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop