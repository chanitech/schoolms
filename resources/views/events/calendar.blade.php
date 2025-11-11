@extends('adminlte::page')

@section('title', 'School Calendar')

@section('content_header')
    <h1>School Calendar</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: "{{ route('events.fetch') }}", // AJAX fetch
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if(info.event.url) {
                window.open(info.event.url, '_blank'); // opens edit page
            }
        },
        editable: false,
        selectable: false
    });
    calendar.render();
});
</script>
@stop
