@extends('adminlte::page')

@section('title', 'Inventory Categories')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-tags mr-2 text-success"></i>Inventory Categories</h1>
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCategoryModal">
        <i class="fas fa-plus mr-1"></i>Add Category
    </button>
</div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="50">Icon</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Items</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="text-center"><i class="{{ $category->icon }} fa-lg text-primary"></i></td>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td class="text-muted">{{ $category->description ?: '—' }}</td>
                        <td>
                            <a href="{{ route('inventory.items', ['category'=>$category->id]) }}" class="badge badge-primary">
                                {{ $category->items_count }} items
                            </a>
                        </td>
                        <td>
                            <button class="btn btn-xs btn-warning btn-edit-cat"
                                data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}"
                                data-icon="{{ $category->icon }}"
                                data-description="{{ $category->description }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('inventory.categories.destroy', $category) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No categories yet. Add one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('inventory.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus mr-1"></i>Add Category</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>FontAwesome Icon Class</label>
                        <input type="text" name="icon" class="form-control" placeholder="fas fa-box" value="fas fa-box">
                        <small class="text-muted">e.g. fas fa-laptop, fas fa-flask</small>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i>Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <input type="text" name="icon" id="edit_icon" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" id="edit_description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
document.querySelectorAll('.btn-edit-cat').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('edit_name').value        = this.dataset.name;
        document.getElementById('edit_icon').value        = this.dataset.icon;
        document.getElementById('edit_description').value = this.dataset.description;
        document.getElementById('editCategoryForm').action =
            '{{ url("inventory/categories") }}/' + this.dataset.id;
        $('#editCategoryModal').modal('show');
    });
});
</script>
@endpush
@endsection
