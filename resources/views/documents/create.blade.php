@extends('adminlte::page')

@section('title', 'Upload Document')

@section('content_header')
    <h1><i class="fas fa-upload text-primary mr-2"></i> Upload Document</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Document Details</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- ── Basic Info ─────────────────────────────────────────── --}}
                    <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Basic Information
                    </h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" placeholder="e.g., Form 4 Mathematics Past Paper 2024" required>
                                @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-control @error('category') is-invalid @enderror" required>
                                    <option value="">-- Select --</option>
                                    @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="2" placeholder="Brief description of this document...">{{ old('description') }}</textarea>
                                @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── Academic Context ───────────────────────────────────── --}}
                    <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-3 mt-2">
                        <i class="fas fa-school mr-1"></i> Academic Context
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Academic Session</label>
                                <select name="academic_session_id" class="form-control @error('academic_session_id') is-invalid @enderror">
                                    <option value="">-- Optional --</option>
                                    @foreach($academicSessions as $s)
                                    <option value="{{ $s->id }}" {{ old('academic_session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                @error('academic_session_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Class / Form Level</label>
                                <select name="class_id" class="form-control @error('class_id') is-invalid @enderror">
                                    <option value="">-- Optional --</option>
                                    @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" list="subjectList"
                                       class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject') }}" placeholder="e.g., Mathematics">
                                <datalist id="subjectList">
                                    @foreach($subjects as $s)
                                    <option value="{{ $s }}">
                                    @endforeach
                                </datalist>
                                @error('subject')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── Document Details ───────────────────────────────────── --}}
                    <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-3 mt-2">
                        <i class="fas fa-tag mr-1"></i> Document Details
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Language</label>
                                <select name="language" class="form-control @error('language') is-invalid @enderror">
                                    @foreach($languages as $lang)
                                    <option value="{{ $lang }}" {{ old('language', 'English') == $lang ? 'selected' : '' }}>{{ $lang }}</option>
                                    @endforeach
                                </select>
                                @error('language')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Document Date</label>
                                <input type="date" name="document_date" class="form-control @error('document_date') is-invalid @enderror"
                                       value="{{ old('document_date') }}">
                                <small class="text-muted">Date the document was issued or created</small>
                                @error('document_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Author / Publisher</label>
                                <input type="text" name="author" class="form-control @error('author') is-invalid @enderror"
                                       value="{{ old('author') }}" placeholder="e.g., Ministry of Education">
                                @error('author')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Tags</label>
                                <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror"
                                       value="{{ old('tags') }}" placeholder="e.g., NECTA, 2024, Biology, Form 4  (comma-separated)">
                                <small class="text-muted">Separate tags with commas — helps with searching</small>
                                @error('tags')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── Visibility ─────────────────────────────────────────── --}}
                    <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-3 mt-2">
                        <i class="fas fa-eye mr-1"></i> Visibility
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-2">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_featured"
                                       name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">
                                    <i class="fas fa-star text-warning mr-1"></i> Pin as Featured
                                    <small class="text-muted d-block">Shows in the featured section on top</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-2">
                                <input type="hidden" name="is_restricted" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_restricted"
                                       name="is_restricted" value="1" {{ old('is_restricted') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_restricted">
                                    <i class="fas fa-lock text-danger mr-1"></i> Restrict to Staff Only
                                    <small class="text-muted d-block">Guardians & students cannot see this</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── File Upload ─────────────────────────────────────────── --}}
                    <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-3 mt-2">
                        <i class="fas fa-paperclip mr-1"></i> File
                    </h6>
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" name="file" id="fileInput"
                                   class="custom-file-input @error('file') is-invalid @enderror"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar,.txt"
                                   required>
                            <label class="custom-file-label" for="fileInput" id="fileLabel">Choose file...</label>
                        </div>
                        @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">
                            Allowed: PDF, Word, Excel, PowerPoint, Images, ZIP &bull; Max size: <strong>20 MB</strong>
                        </small>
                    </div>

                    {{-- File preview --}}
                    <div id="filePreview" class="d-none border rounded p-2 mb-3 d-flex align-items-center" style="gap:.7rem;background:#f8f9fa">
                        <i id="previewIcon" class="fas fa-file-alt fa-2x text-secondary"></i>
                        <div>
                            <div id="previewName" class="font-weight-bold" style="font-size:.88rem"></div>
                            <div id="previewSize" class="text-muted" style="font-size:.75rem"></div>
                        </div>
                    </div>

                    <div class="d-flex mt-3" style="gap:.6rem">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload mr-1"></i> Upload Document
                        </button>
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
document.getElementById('fileInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    document.getElementById('fileLabel').textContent = file.name;

    const icons = {
        'application/pdf': 'fas fa-file-pdf text-danger',
        'application/msword': 'fas fa-file-word text-primary',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fas fa-file-word text-primary',
        'application/vnd.ms-excel': 'fas fa-file-excel text-success',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'fas fa-file-excel text-success',
        'application/vnd.ms-powerpoint': 'fas fa-file-powerpoint text-warning',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'fas fa-file-powerpoint text-warning',
    };
    const icon = icons[file.type] || (file.type.startsWith('image/') ? 'fas fa-file-image text-info' : 'fas fa-file-alt text-secondary');
    const size = file.size >= 1048576
        ? (file.size / 1048576).toFixed(1) + ' MB'
        : (file.size / 1024).toFixed(1) + ' KB';

    document.getElementById('previewIcon').className = icon + ' fa-2x';
    document.getElementById('previewName').textContent = file.name;
    document.getElementById('previewSize').textContent = size;

    const preview = document.getElementById('filePreview');
    preview.classList.remove('d-none');
    preview.classList.add('d-flex');
});
</script>
@endpush
