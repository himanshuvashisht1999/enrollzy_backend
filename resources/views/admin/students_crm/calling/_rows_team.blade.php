{{-- Calling dashboard, Team Assigned Leads rows (also loaded by "See more": $offset = rows already shown) --}}
@foreach($rows as $assignment)
                                    @php 
                                        $customer = $assignment->customer;
                                        $leadType = $assignment->lead_type ?? 'new';
                                        
                                        $statusTag = '<span class="status-tag tag-new"><i class="fas fa-sparkles"></i> New Lead</span>';
                                        if ($leadType === 'overdue') {
                                            $statusTag = '<span class="status-tag tag-overdue"><i class="fas fa-exclamation-circle"></i> Overdue</span>';
                                        } elseif ($leadType === 'due_today') {
                                            $statusTag = '<span class="status-tag tag-due"><i class="fas fa-clock"></i> Due Today</span>';
                                        } elseif ($leadType === 'reassigned') {
                                            $statusTag = '<span class="status-tag tag-reassigned"><i class="fas fa-random"></i> Reassigned Lead</span>';
                                        } elseif ($leadType === 'upcoming') {
                                            // a follow-up after the selected range: its date, not "Due Today"
                                            $statusTag = '<span class="status-tag tag-reassigned"><i class="far fa-calendar-alt"></i> Follow-up ' . e(\Carbon\Carbon::parse($assignment->latest_history->date_required)->format('d M Y')) . '</span>';
                                        }
                                    @endphp
                                    @if($customer)
                                    <tr>
                                        @if($isTopLevel)<td><input type="checkbox" class="form-check-input bulk-pick" value="{{ $customer->id }}" aria-label="Select {{ $customer->name }}"></td>@endif
                                        <td class="text-muted fw-bold small" style="width: 40px;">
                                            #{{ ($offset ?? 0) + $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6">{{ $customer->name }} <span class="badge bg-light text-secondary border fw-normal ms-1" style="font-size: 0.7rem;">ID #{{ $customer->id }}</span>@include('admin.calling._star', ['id' => $customer->id])</div>
                                            <div class="text-muted small">{{ $customer->city ?? 'Unknown' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $assignment->staff->name ?? 'Unknown' }}</div>
                                        </td>
                                        <td>
                                            {!! $statusTag !!}
                                            @if(isset($assignment->latest_history) && $assignment->latest_history && $assignment->latest_history->calling_status)
                                                <div class="text-muted small mt-1 fw-medium">
                                                    @if($leadType === 'reassigned')
                                                        <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 0.7rem;"><i class="fas fa-history me-1 opacity-75"></i>Last: {{ $assignment->latest_history->calling_status->name }}</span>
                                                    @else
                                                        {{ $assignment->latest_history->calling_status->name }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ \Carbon\Carbon::parse($assignment->updated_at)->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-reassign-action open-reassign-modal" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}">
                                                <i class="fas fa-user-edit me-1"></i> Reassign Lead
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
@endforeach
