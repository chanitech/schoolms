@php $bus = $bus ?? null; @endphp

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Plate Number <span class="text-danger">*</span></label>
        <input type="text" name="plate_number" class="form-control" required
               value="{{ old('plate_number', $bus->plate_number ?? '') }}" placeholder="e.g. T123 ABC">
    </div>
    <div class="form-group col-md-4">
        <label>Bus Name / Label</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $bus->name ?? '') }}" placeholder="e.g. Bus 1">
    </div>
    <div class="form-group col-md-4">
        <label>Capacity <span class="text-danger">*</span></label>
        <input type="number" name="capacity" min="1" max="200" class="form-control" required value="{{ old('capacity', $bus->capacity ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Driver</label>
        <select name="driver_staff_id" class="form-control">
            <option value="">— No driver assigned —</option>
            @foreach($drivers as $d)
                <option value="{{ $d->id }}" {{ old('driver_staff_id', $bus->driver_staff_id ?? '') == $d->id ? 'selected' : '' }}>
                    {{ $d->first_name }} {{ $d->last_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            @foreach(['active' => 'Active', 'maintenance' => 'In Maintenance', 'inactive' => 'Inactive'] as $val => $label)
                <option value="{{ $val }}" {{ old('status', $bus->status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label>Notes</label>
    <textarea name="notes" rows="2" class="form-control">{{ old('notes', $bus->notes ?? '') }}</textarea>
</div>
