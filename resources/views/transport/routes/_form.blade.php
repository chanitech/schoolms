@php $route = $route ?? null; @endphp

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Route Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required
               value="{{ old('name', $route->name ?? '') }}" placeholder="e.g. Route A — Mbezi">
    </div>
    <div class="form-group col-md-3">
        <label>Bus</label>
        <select name="bus_id" class="form-control">
            <option value="">— No bus assigned yet —</option>
            @foreach($buses as $b)
                <option value="{{ $b->id }}" {{ old('bus_id', $route->bus_id ?? '') == $b->id ? 'selected' : '' }}>
                    {{ $b->plate_number }} {{ $b->name ? '(' . $b->name . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            <option value="active" {{ old('status', $route->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $route->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Monthly Fee (TZS) <span class="text-danger">*</span></label>
        <input type="number" name="monthly_fee" step="0.01" min="0" class="form-control" required
               value="{{ old('monthly_fee', $route->monthly_fee ?? '') }}">
    </div>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" rows="2" class="form-control" placeholder="Areas covered, notes...">{{ old('description', $route->description ?? '') }}</textarea>
</div>
