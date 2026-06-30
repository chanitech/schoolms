<form method="POST" action="{{ route('timetables.log-session', $entry) }}">
    @csrf
    <input type="hidden" name="session_date" value="{{ $date->toDateString() }}">
    <input type="hidden" name="week" value="{{ $weekOffset }}">
    @if($teacherId ?? null) <input type="hidden" name="teacher_id" value="{{ $teacherId }}"> @endif
    <div class="input-group input-group-sm mb-1">
        <select name="status" class="form-control form-control-sm">
            <option value="attended"  @selected(($currentStatus ?? '') === 'attended')>Attended</option>
            <option value="late"      @selected(($currentStatus ?? '') === 'late')>Late</option>
            <option value="absent"    @selected(($currentStatus ?? '') === 'absent')>Absent</option>
            <option value="other"     @selected(($currentStatus ?? '') === 'other')>Other</option>
        </select>
    </div>
    <div class="input-group input-group-sm mb-1">
        <textarea name="notes" class="form-control form-control-sm"
                  rows="2" placeholder="Notes (optional)">{{ $currentNotes ?? '' }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary btn-sm btn-block">
        <i class="fas fa-save mr-1"></i> Update
    </button>
</form>
