@extends('adminlte::page')

@section('title', 'Document Library')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="mb-0"><i class="fas fa-book-open text-warning mr-2"></i> Document Library</h1>
        <small class="text-muted">Browse, search and download school documents</small>
    </div>
    @can('upload documents')
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="fas fa-upload mr-1"></i> Upload Document
    </a>
    @endcan
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

{{-- ── Featured / Pinned ──────────────────────────────────────────────────── --}}
@if($featured->isNotEmpty())
<div class="mb-4">
    <h5 class="font-weight-bold text-warning mb-2"><i class="fas fa-star mr-1"></i> Featured Documents</h5>
    <div class="row">
        @foreach($featured->take(4) as $doc)
        <div class="col-md-3 mb-2">
            <a href="{{ route('documents.show', $doc->id) }}" class="text-decoration-none">
                <div class="card border-warning h-100" style="border-width:2px!important;border-radius:8px">
                    <div class="card-body py-2 px-3 d-flex align-items-center" style="gap:.7rem">
                        <i class="{{ $doc->icon_class }}" style="font-size:1.6rem;flex-shrink:0"></i>
                        <div style="min-width:0">
                            <div class="font-weight-bold text-dark" style="font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $doc->title }}
                            </div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ \App\Models\Document::categories()[$doc->category] ?? $doc->category }}
                                &bull; {{ $doc->file_size_human }}
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Filter Panel ────────────────────────────────────────────────────────── --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center" id="filterHeading" style="cursor:pointer" data-toggle="collapse" data-target="#filterBody">
        <span class="font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter & Search</span>
        <i class="fas fa-chevron-down text-muted" style="font-size:.8rem"></i>
    </div>
    <div class="collapse {{ request()->anyFilled(['search','category','session_id','class_id','subject','language','sort']) ? 'show' : '' }}" id="filterBody">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('documents.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search title, author, subject..."
                                   value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="category" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="session_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Sessions</option>
                            @foreach($academicSessions as $s)
                            <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="class_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="language" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Languages</option>
                            @foreach($languages as $lang)
                            <option value="{{ $lang }}" {{ request('language') == $lang ? 'selected' : '' }}>{{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="latest"    {{ request('sort','latest') == 'latest'    ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest"    {{ request('sort') == 'oldest'    ? 'selected' : '' }}>Oldest First</option>
                            <option value="title"     {{ request('sort') == 'title'     ? 'selected' : '' }}>Title A–Z</option>
                            <option value="downloads" {{ request('sort') == 'downloads' ? 'selected' : '' }}>Most Downloaded</option>
                            <option value="size"      {{ request('sort') == 'size'      ? 'selected' : '' }}>Largest File</option>
                        </select>
                    </div>
                    @if(request()->anyFilled(['search','category','session_id','class_id','subject','language','sort']))
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('documents.index') }}" class="btn btn-sm btn-secondary btn-block">
                            <i class="fas fa-times mr-1"></i> Clear All
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Category Quick Tabs ─────────────────────────────────────────────────── --}}
<div class="mb-3 d-flex flex-wrap" style="gap:.35rem">
    <a href="{{ route('documents.index', request()->except('category')) }}"
       class="badge badge-pill {{ !request('category') ? 'badge-primary' : 'badge-light border' }}"
       style="font-size:.8rem;padding:.4em .85em;text-decoration:none;color:{{ !request('category') ? '#fff' : '#555' }}">
        All
    </a>
    @foreach($categories as $key => $label)
    @php $cnt = \App\Models\Document::where('category', $key)->count(); @endphp
    @if($cnt > 0)
    <a href="{{ route('documents.index', array_merge(request()->except('category'), ['category' => $key])) }}"
       class="badge badge-pill {{ request('category') == $key ? 'badge-primary' : 'badge-light border' }}"
       style="font-size:.8rem;padding:.4em .85em;text-decoration:none;color:{{ request('category') == $key ? '#fff' : '#555' }}">
        {{ $label }} ({{ $cnt }})
    </a>
    @endif
    @endforeach
</div>

{{-- ── Documents Grid ──────────────────────────────────────────────────────── --}}
@if($documents->isEmpty())
<div class="text-center py-5 text-muted">
    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
    <p class="mb-0">No documents found matching your filters.</p>
    @can('upload documents')
    <a href="{{ route('documents.create') }}" class="btn btn-primary mt-3">
        <i class="fas fa-upload mr-1"></i> Upload First Document
    </a>
    @endcan
</div>
@else
<div class="row" id="docsGrid">
    @foreach($documents as $doc)
    <div class="col-md-4 col-lg-3 mb-3">
        <div class="card h-100 shadow-sm" style="border-radius:10px;border:1px solid #e9ecef;transition:box-shadow .15s"
             onmouseenter="this.style.boxShadow='0 4px 18px rgba(0,0,0,.11)'"
             onmouseleave="this.style.boxShadow=''">

            {{-- Badges row --}}
            <div class="px-3 pt-2 d-flex justify-content-between align-items-center" style="gap:.3rem">
                <span class="badge badge-light border" style="font-size:.7rem">
                    {{ \App\Models\Document::categories()[$doc->category] ?? $doc->category }}
                </span>
                <div class="d-flex" style="gap:.3rem">
                    @if($doc->is_featured)
                    <span class="badge badge-warning" title="Featured"><i class="fas fa-star"></i></span>
                    @endif
                    @if($doc->is_restricted)
                    <span class="badge badge-danger" title="Staff only"><i class="fas fa-lock"></i></span>
                    @endif
                </div>
            </div>

            <div class="card-body py-2 px-3">
                <div class="mb-2">
                    <i class="{{ $doc->icon_class }}" style="font-size:1.8rem"></i>
                </div>
                <h6 class="font-weight-bold mb-1" style="font-size:.88rem;line-height:1.3;word-break:break-word">
                    {{ Str::limit($doc->title, 60) }}
                </h6>

                @if($doc->description)
                <p class="text-muted mb-1" style="font-size:.76rem;line-height:1.4">
                    {{ Str::limit($doc->description, 75) }}
                </p>
                @endif

                <div class="mt-1" style="font-size:.72rem;color:#888;line-height:1.8">
                    @if($doc->subject)
                    <div><i class="fas fa-book mr-1"></i>{{ $doc->subject }}</div>
                    @endif
                    @if($doc->schoolClass)
                    <div><i class="fas fa-users mr-1"></i>{{ $doc->schoolClass->name }}</div>
                    @endif
                    @if($doc->academicSession)
                    <div><i class="fas fa-calendar mr-1"></i>{{ $doc->academicSession->name }}</div>
                    @endif
                    <div>
                        <i class="fas fa-weight-hanging mr-1"></i>{{ $doc->file_size_human }}
                        &nbsp;&bull;&nbsp;
                        <i class="fas fa-download mr-1"></i>{{ number_format($doc->download_count) }}
                        &nbsp;&bull;&nbsp;
                        {{ $doc->language }}
                    </div>
                    <div><i class="fas fa-clock mr-1"></i>{{ $doc->created_at->diffForHumans() }}</div>
                </div>

                @if($doc->tags_list)
                <div class="mt-1 d-flex flex-wrap" style="gap:.25rem">
                    @foreach(array_slice($doc->tags_list, 0, 3) as $tag)
                    <span class="badge" style="background:#e8f4fd;color:#1976d2;font-size:.67rem;border-radius:20px">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="card-footer bg-transparent px-2 py-2" style="border-top:1px solid #f0f0f0">
                <div class="d-flex flex-wrap" style="gap:.3rem">
                    <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-sm btn-outline-info flex-fill" style="font-size:.75rem">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline-success flex-fill" style="font-size:.75rem">
                        <i class="fas fa-download"></i> Download
                    </a>
                    @can('upload documents')
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-toggle="dropdown" style="font-size:.75rem">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm" style="font-size:.82rem;min-width:160px">
                            <form action="{{ route('documents.toggle-featured', $doc->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-star mr-2 {{ $doc->is_featured ? 'text-warning' : 'text-muted' }}"></i>
                                    {{ $doc->is_featured ? 'Unpin' : 'Pin as Featured' }}
                                </button>
                            </form>
                            <form action="{{ route('documents.toggle-restricted', $doc->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-lock mr-2 {{ $doc->is_restricted ? 'text-danger' : 'text-muted' }}"></i>
                                    {{ $doc->is_restricted ? 'Make Public' : 'Restrict to Staff' }}
                                </button>
                            </form>
                            @can('delete documents')
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('documents.destroy', $doc->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger"
                                        onclick="return confirm('Delete this document permanently?')">
                                    <i class="fas fa-trash mr-2"></i> Delete
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-2 d-flex justify-content-between align-items-center">
    <small class="text-muted">Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }} documents</small>
    {{ $documents->links() }}
</div>
@endif

@stop
