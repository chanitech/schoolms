@extends('adminlte::page')

@section('title', $document->title)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.4rem" class="mb-0">
        <i class="{{ $document->icon_class }} mr-2"></i>{{ $document->title }}
    </h1>
    <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to Library
    </a>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="row">

    {{-- ── Left: Preview ──────────────────────────────────────────────────── --}}
    <div class="col-md-8">

        @if($document->is_pdf)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span><i class="fas fa-file-pdf text-danger mr-1"></i> Document Preview</span>
                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-download mr-1"></i> Download ({{ $document->file_size_human }})
                </a>
            </div>
            <div class="card-body p-0">
                <iframe src="{{ asset('storage/' . $document->file_path) }}"
                        style="width:100%;height:78vh;border:none;border-radius:0 0 4px 4px"></iframe>
            </div>
        </div>

        @elseif($document->is_image)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span><i class="fas fa-image text-info mr-1"></i> Image Preview</span>
                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-download mr-1"></i> Download ({{ $document->file_size_human }})
                </a>
            </div>
            <div class="card-body text-center bg-dark" style="border-radius:0 0 4px 4px">
                <img src="{{ asset('storage/' . $document->file_path) }}"
                     class="img-fluid rounded" style="max-height:75vh"
                     alt="{{ $document->title }}">
            </div>
        </div>

        @else
        <div class="card mb-3">
            <div class="card-body text-center py-5">
                <i class="{{ $document->icon_class }}" style="font-size:5rem"></i>
                <p class="mt-3 text-muted mb-1">Preview not available for this file type.</p>
                <p class="text-muted" style="font-size:.85rem">{{ $document->original_name }}</p>
                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success btn-lg mt-2">
                    <i class="fas fa-download mr-1"></i> Download to Open
                </a>
            </div>
        </div>
        @endif

        {{-- ── Related Documents ──────────────────────────────────────────── --}}
        @if($related->isNotEmpty())
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title" style="font-size:.95rem">
                    <i class="fas fa-link mr-1"></i> Related Documents
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($related as $rel)
                    <li class="list-group-item py-2 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center" style="gap:.6rem;min-width:0">
                            <i class="{{ $rel->icon_class }}"></i>
                            <div style="min-width:0">
                                <a href="{{ route('documents.show', $rel->id) }}" class="font-weight-bold text-dark d-block"
                                   style="font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    {{ $rel->title }}
                                </a>
                                <span class="text-muted" style="font-size:.72rem">
                                    {{ $rel->file_size_human }} &bull; {{ $rel->created_at->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('documents.download', $rel->id) }}" class="btn btn-xs btn-outline-success ml-2" style="font-size:.75rem;padding:.2rem .5rem">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Right: Details Panel ─────────────────────────────────────────────── --}}
    <div class="col-md-4">

        {{-- Download button --}}
        <a href="{{ route('documents.download', $document->id) }}"
           class="btn btn-success btn-block mb-3" style="font-size:1rem;padding:.6rem">
            <i class="fas fa-download mr-2"></i> Download &nbsp;
            <span class="badge badge-light text-success">{{ $document->file_size_human }}</span>
        </a>

        {{-- Metadata card --}}
        <div class="card card-outline card-primary mb-3">
            <div class="card-header py-2">
                <h3 class="card-title" style="font-size:.9rem"><i class="fas fa-info-circle mr-1"></i> Details</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0" style="font-size:.82rem">
                    <tr>
                        <td class="text-muted pl-3" style="width:38%">Category</td>
                        <td>
                            <span class="badge badge-primary" style="font-size:.75rem">
                                {{ \App\Models\Document::categories()[$document->category] ?? $document->category }}
                            </span>
                        </td>
                    </tr>
                    @if($document->subject)
                    <tr>
                        <td class="text-muted pl-3">Subject</td>
                        <td>{{ $document->subject }}</td>
                    </tr>
                    @endif
                    @if($document->schoolClass)
                    <tr>
                        <td class="text-muted pl-3">Class</td>
                        <td>{{ $document->schoolClass->name }}</td>
                    </tr>
                    @endif
                    @if($document->academicSession)
                    <tr>
                        <td class="text-muted pl-3">Session</td>
                        <td>{{ $document->academicSession->name }}</td>
                    </tr>
                    @endif
                    @if($document->author)
                    <tr>
                        <td class="text-muted pl-3">Author</td>
                        <td>{{ $document->author }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted pl-3">Language</td>
                        <td>{{ $document->language }}</td>
                    </tr>
                    @if($document->document_date)
                    <tr>
                        <td class="text-muted pl-3">Doc Date</td>
                        <td>{{ $document->document_date->format('d M Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted pl-3">File Name</td>
                        <td style="word-break:break-all">{{ $document->original_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">File Size</td>
                        <td>{{ $document->file_size_human }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Downloads</td>
                        <td>
                            <i class="fas fa-download text-success mr-1"></i>
                            {{ number_format($document->download_count) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Uploaded by</td>
                        <td>{{ $document->uploader?->name ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Upload Date</td>
                        <td>{{ $document->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    @if($isStaff)
                    <tr>
                        <td class="text-muted pl-3">Visibility</td>
                        <td>
                            @if($document->is_restricted)
                            <span class="badge badge-danger">Staff Only</span>
                            @else
                            <span class="badge badge-success">Public</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Description --}}
        @if($document->description)
        <div class="card mb-3">
            <div class="card-header py-2">
                <h3 class="card-title" style="font-size:.9rem"><i class="fas fa-align-left mr-1"></i> Description</h3>
            </div>
            <div class="card-body py-2" style="font-size:.85rem">
                {{ $document->description }}
            </div>
        </div>
        @endif

        {{-- Tags --}}
        @if($document->tags_list)
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap" style="gap:.3rem">
                    @foreach($document->tags_list as $tag)
                    <a href="{{ route('documents.index', ['search' => $tag]) }}"
                       class="badge" style="background:#e8f4fd;color:#1565c0;font-size:.78rem;border-radius:20px;padding:.4em .8em;text-decoration:none">
                        <i class="fas fa-tag mr-1" style="font-size:.65rem"></i>{{ $tag }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Staff Actions --}}
        @can('upload documents')
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title" style="font-size:.9rem"><i class="fas fa-cog mr-1"></i> Actions</h3>
            </div>
            <div class="card-body py-2 d-flex flex-column" style="gap:.4rem">
                <form action="{{ route('documents.toggle-featured', $document->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning btn-block">
                        <i class="fas fa-star mr-1"></i>
                        {{ $document->is_featured ? 'Unpin from Featured' : 'Pin as Featured' }}
                    </button>
                </form>
                <form action="{{ route('documents.toggle-restricted', $document->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary btn-block">
                        <i class="fas fa-{{ $document->is_restricted ? 'unlock' : 'lock' }} mr-1"></i>
                        {{ $document->is_restricted ? 'Make Public' : 'Restrict to Staff Only' }}
                    </button>
                </form>
                @can('delete documents')
                <form action="{{ route('documents.destroy', $document->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger btn-block"
                            onclick="return confirm('Permanently delete this document?')">
                        <i class="fas fa-trash mr-1"></i> Delete Document
                    </button>
                </form>
                @endcan
            </div>
        </div>
        @endcan

    </div>
</div>

@stop
