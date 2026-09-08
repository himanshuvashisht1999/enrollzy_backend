@extends('admin.layouts.master')

@section('title', 'Work Management - Calendar')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<style>
    .fc-event { cursor: pointer; border-radius: 6px; padding: 2px 5px; font-size: 0.85rem; font-weight: 500; border: none; }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700; color: #333; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="far fa-calendar-alt text-primary me-2"></i> Work & Meetings Calendar</h4>
            <p class="text-muted small mb-0">Unified schedule of task deadlines, milestone targets, and project meetings</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.work_management.tasks.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-plus me-1"></i> Add Task
            </a>
            <a href="{{ route('admin.work_management.meetings.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="far fa-calendar-plus me-1"></i> Schedule Meeting
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div id="calendar"></div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: "{{ route('admin.work_management.dashboard.calendar_events') }}",
            eventClick: function(info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                    info.jsEvent.preventDefault();
                }
            }
        });
        calendar.render();
    });
</script>
@endpush
