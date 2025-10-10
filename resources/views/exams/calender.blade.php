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
        events: @json($events),
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // prevent default navigation
            if(info.event.url) {
                window.open(info.event.url, '_blank');
            }
        },
        editable: false,
        selectable: false
    });
    calendar.render();
});
</script>
@stop
