@if ($attendance->isEmpty())
    <p>No attendance records found for this date.</p>
@else
    <style>
        .attendance-summary {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .attendance-roadmap {
            padding-top: 10px;
        }

        .timeline {
            list-style: none;
            padding-left: 0;
            position: relative;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20px;
            width: 2px;
            background: #007bff;
        }

        .timeline li {
            position: relative;
            padding: 10px 0;
            padding-left: 40px;
        }

        .timeline li::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #007bff;
        }

        .timeline li .timeline-content {
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .timeline li .timeline-content strong {
            display: block;
            color: #343a40;
        }

        .timeline li .timeline-content p {
            margin: 0;
            color: #6c757d;
        }

        /* Additional styling to match your design */
        .timeline li .timeline-content span.time {
            font-weight: bold;
            color: #007bff;
        }
    </style>
    <div class="attendance-summary">
        <h6>Work Summary of Date : @foreach($attendance as $attend){{ \Carbon\Carbon::parse($attend->date)->format('Y-m-d') }}@endforeach</h6>
        @php
            // Helper function to convert minutes to hours and minutes
            function convertMinutesToHours($minutes)
            {
                if ($minutes === null) {
                    return 'N/A';
                }

                $hours = floor($minutes / 60);
                $remainingMinutes = $minutes % 60;

                return "{$hours} hour" .
                    ($hours !== 1 ? 's' : '') .
                    " and {$remainingMinutes} minute" .
                    ($remainingMinutes !== 1 ? 's' : '');
            }

            $totalWorkFormatted = convertMinutesToHours($totalWork);
            $totalBreakFormatted = convertMinutesToHours($totalBreak);
        @endphp
        <p>Total Work Duration: <strong>{{ $totalWorkFormatted }}</strong></p>
        <p>Total Break Duration: <strong>{{ $totalBreakFormatted }}</strong></p>
    </div>
    <div class="card row">
        @foreach ($attendance as $attand)
            <div class="col-lg-12">
                <div class="attendance-roadmap">
                    <h6>Check-ins, Check-outs All Times</h6>
                    <ul class="timeline">
                        <li>
                            <strong>{{ date('h:i A', strtotime($attand->check_in)) }}</strong> - Checked In
                            @if ($attand->comments)
                                <br><em>{{ $attand->comments }}</em>
                            @endif
                        </li>
                        @if ($attand->breaks)
                            @foreach ($attand->breaks as $break)
                                <li style="padding-left: 70px !important">
                                    <strong>{{ date('h:i A', strtotime($break->start)) }}</strong> - {{ $break->type }}
                                    Break Start
                                    @if ($break->reason)
                                        <br>Reason : <em>{{ $break->reason }}</em>
                                    @endif
                                </li>
                                @if ($break->end)
                                    <li style="padding-left: 70px !important">
                                        <strong>{{ date('h:i A', strtotime($break->end)) }}</strong> -
                                        {{ $break->type }} Break end
                                        @if ($break->comments)
                                            <br>Comment : <em>{{ $break->comments }}</em>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        @endif
                        @if ($attand->check_out)
                            <li>
                                <strong>{{ date('h:i A', strtotime($attand->check_out)) }}</strong> - Checked Out
                                @if ($attand->comments)
                                    <br><em>{{ $attand->comments }}</em>
                                @endif
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endif
