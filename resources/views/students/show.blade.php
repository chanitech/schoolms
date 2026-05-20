@extends('adminlte::page')

@section('title', 'Student Profile - ' . $student->full_name)

@section('content_header')
    <h1><i class="fas fa-user-graduate"></i> Student Profile</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        {{-- Student Photo Card --}}
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if($student->photo)
                        <img class="profile-user-img img-fluid img-circle" src="{{ Storage::url($student->photo) }}" alt="Student photo">
                    @else
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset('vendor/adminlte/dist/img/avatar.png') }}" alt="Default avatar">
                    @endif
                </div>
                <h3 class="profile-username text-center">{{ $student->full_name }}</h3>
                <p class="text-muted text-center">
                    {{ $student->admission_no }} 
                    @if($student->class)
                        | {{ $student->class->name }}
                    @endif
                </p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Status</b> 
                        <span class="badge badge-{{ $student->status_badge_class }} float-right">{{ ucfirst($student->status) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Gender</b> <span class="float-right">{{ $student->gender_label }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Date of Birth</b> <span class="float-right">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Age</b> <span class="float-right">{{ $student->age ?? '-' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Contact Card --}}
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-address-card"></i> Contact Information</h3>
            </div>
            <div class="card-body">
                <p><i class="fas fa-phone"></i> <strong>Phone:</strong> {{ $student->phone ?? '-' }}</p>
                <p><i class="fas fa-envelope"></i> <strong>Email:</strong> {{ $student->email ?? '-' }}</p>
                <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong> {{ $student->address ?? '-' }}</p>
                <p><i class="fas fa-id-card"></i> <strong>National ID:</strong> {{ $student->national_id ?? '-' }}</p>
            </div>
        </div>

        {{-- Guardian Card --}}
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-friends"></i> Guardian Information</h3>
            </div>
            <div class="card-body">
                @if($student->guardian)
                    <p><strong>Name:</strong> {{ $student->guardian->first_name }} {{ $student->guardian->last_name }}</p>
                    <p><strong>Phone:</strong> {{ $student->guardian->phone ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $student->guardian->email ?? '-' }}</p>
                    <p><strong>Relation:</strong> {{ $student->guardian->relation ?? '-' }}</p>
                    <p><strong>Address:</strong> {{ $student->guardian->address ?? '-' }}</p>
                @else
                    <p class="text-muted">No guardian assigned.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Academic Information Card --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Academic Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl>
                            <dt>Class</dt>
                            <dd>{{ $student->class->name ?? '-' }}</dd>
                            <dt>Department</dt>
                            <dd>{{ $student->department->name ?? '-' }}</dd>
                            <dt>Academic Session</dt>
                            <dd>{{ $student->academicSession->name ?? '-' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl>
                            <dt>Admission Date</dt>
                            <dd>{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : '-' }}</dd>
                            <dt>Enrollment Status</dt>
                            <dd>{{ ucfirst($student->status) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dormitory & Bed Allocation Card --}}
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bed"></i> Dormitory & Bed Allocation</h3>
            </div>
            <div class="card-body">
                @if($bedDetails)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>Currently Allocated</strong>
                    </div>
                    <table class="table table-bordered">
                        <tr><th style="width: 40%">Dormitory</th><td>{{ $bedDetails->dormitory }}</td></tr>
                        <tr><th>Room Number</th><td>{{ $bedDetails->room_number }} (Floor {{ $bedDetails->floor }})</td></tr>
                        <tr><th>Bed Number</th><td>{{ $bedDetails->bed_number }} ({{ ucfirst(str_replace('_', ' ', $bedDetails->bed_type)) }})</td></tr>
                    </table>
                @else
                    <p class="text-muted"><i class="fas fa-info-circle"></i> No bed allocation found.</p>
                @endif
            </div>
        </div>

        {{-- Recent Marks / Results Card --}}
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Recent Exam Results</h3>
                <div class="card-tools">
                    <a href="{{ route('results.show', $student) }}" class="btn btn-sm btn-primary">Full Results</a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Mark</th>
                            <th>Grade</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recentMarks = $student->marks()->with(['exam', 'subject'])->orderBy('created_at', 'desc')->limit(10)->get();
                        @endphp
                        @forelse($recentMarks as $mark)
                        <tr>
                            <td>{{ $mark->exam->name ?? '-' }}</td>
                            <td>{{ $mark->subject->name ?? '-' }}</td>
                            <td class="{{ $mark->mark >= 50 ? 'text-success' : ($mark->mark >= 30 ? 'text-warning' : 'text-danger') }}">{{ $mark->mark }}%</td>
                            <td>{{ $mark->grade->name ?? '-' }}</td>
                            <td>{{ $mark->grade->point ?? '-' }}</td>
                        </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No marks recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Allocation History Card (if any) --}}
        @if($student->bedAllocations->count() > 0)
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Bed Allocation History</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Dormitory</th>
                            <th>Room</th>
                            <th>Bed</th>
                            <th>Allocation Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->bedAllocations as $alloc)
                        <tr>
                            <td>{{ $alloc->bed->room->dormitory->name ?? '-' }}</td>
                            <td>{{ $alloc->bed->room->room_number ?? '-' }}</td>
                            <td>{{ $alloc->bed->bed_number ?? '-' }}</td>
                            <td>{{ $alloc->allocation_date ? $alloc->allocation_date->format('d M Y') : '-' }}</td>
                            <td>{{ $alloc->end_date ? $alloc->end_date->format('d M Y') : '-' }}</td>
                            <td><span class="badge badge-{{ $alloc->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($alloc->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('students.edit', $student) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Student</a>
                <a href="{{ route('results.show', $student) }}" class="btn btn-info"><i class="fas fa-chart-bar"></i> View Full Results</a>
                @if(!$bedDetails)
                    <a href="{{ route('dormitories.allocations.create') }}?student_id={{ $student->id }}" class="btn btn-success"><i class="fas fa-bed"></i> Allocate Bed</a>
                @endif
                <form action="{{ route('students.destroy', $student) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?')"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop