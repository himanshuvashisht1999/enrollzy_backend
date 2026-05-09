@if ($attendance->isEmpty())
    <div class="text-center py-4">
        <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
        <p class="text-muted">No attendance records found for this date.</p>
    </div>
@else
    @php
        function formatMinutes($minutes) {
            $h = floor($minutes / 60);
            $m = $minutes % 60;
            return "{$h}h {$m}m";
        }
    @endphp
    <div class="mb-4 text-center">
        <div class="row g-2">
            <div class="col-6">
                <div class="p-3 bg-light rounded-4">
                    <div class="text-muted small">Total Work</div>
                    <div class="fw-bold text-primary">{{ formatMinutes($totalWork) }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-light rounded-4">
                    <div class="text-muted small">Total Breaks</div>
                    <div class="fw-bold text-danger">{{ formatMinutes($totalBreak) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="attendance-timeline">
        @foreach ($attendance as $att)
        <div class="card border-0 bg-light-soft mb-3 rounded-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white p-2 rounded-3 me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6 class="m-0 fw-bold">Session: {{ date('h:i A', strtotime($att->check_in)) }} - {{ $att->check_out ? date('h:i A', strtotime($att->check_out)) : 'Active' }}</h6>
                </div>
                
                <div class="ps-4 border-start border-2 ms-3">
                    <div class="mb-3 position-relative">
                        <span class="position-absolute top-0 start-0 translate-middle p-1 bg-success border border-light rounded-circle" style="left: -17px !important; top: 10px !important;"></span>
                        <div class="fw-bold small">{{ date('h:i A', strtotime($att->check_in)) }}</div>
                        <div class="text-muted small">Checked In</div>
                    </div>

                    @foreach ($att->breaks as $break)
                    <div class="mb-3 position-relative">
                        <span class="position-absolute top-0 start-0 translate-middle p-1 bg-warning border border-light rounded-circle" style="left: -17px !important; top: 10px !important;"></span>
                        <div class="fw-bold small">{{ date('h:i A', strtotime($break->start)) }}</div>
                        <div class="text-muted small">{{ ucfirst($break->type) }} Break Started</div>
                        @if($break->reason) <div class="small italic text-muted">"{{ $break->reason }}"</div> @endif
                        
                        @if($break->end)
                        <div class="mt-2 text-warning small fw-bold">
                            <i class="fas fa-redo fa-xs me-1"></i> {{ date('h:i A', strtotime($break->end)) }} Resumed
                        </div>
                        @endif
                    </div>
                    @endforeach

                    @if ($att->check_out)
                    <div class="position-relative">
                        <span class="position-absolute top-0 start-0 translate-middle p-1 bg-danger border border-light rounded-circle" style="left: -17px !important; top: 10px !important;"></span>
                        <div class="fw-bold small">{{ date('h:i A', strtotime($att->check_out)) }}</div>
                        <div class="text-muted small">Checked Out</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
