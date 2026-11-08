{{-- Calling dashboard, Your Calling Queue rows (also loaded by "See more": $offset = rows already shown) --}}
@foreach($rows as $item)
                                    @php 
                                        $customer = $item['customer'];
                                        $history = $item['history'];
                                        $type = $item['type'];
                                        
                                        $statusTag = '<span class="status-tag tag-new"><i class="fas fa-sparkles"></i> New Lead</span>';
                                        if ($type === 'overdue') {
                                            $statusTag = '<span class="status-tag tag-overdue"><i class="fas fa-exclamation-circle"></i> Overdue Call</span>';
                                        } elseif ($type === 'due_today') {
                                            $statusTag = '<span class="status-tag tag-due"><i class="fas fa-clock"></i> Due Today</span>';
                                        } elseif ($type === 'reassigned') {
                                            $statusTag = '<span class="status-tag tag-reassigned"><i class="fas fa-random"></i> Reassigned Lead</span>';
                                        }
                                        
                                        $maskedPhone = substr($customer->phone, 0, 2) . 'XXXXX' . substr($customer->phone, -3);
                                        $isUnlocked = (isset($unlocked_lead_id) && $unlocked_lead_id == $customer->id);
                                    @endphp
                                    <tr>
                                        <td class="text-muted fw-bold small" style="width: 40px;">
                                             #{{ ($offset ?? 0) + $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6">{{ $customer->name }} <span class="badge bg-light text-secondary border fw-normal ms-1" style="font-size: 0.7rem;">ID #{{ $customer->id }}</span>@include('admin.calling._star', ['id' => $customer->id])</div>
                                            <div class="text-muted small mt-0.5">
                                                @if(!empty($customer->interested_in_course))
                                                    <span class="text-primary fw-semibold">{{ $customer->interested_in_course }}</span>
                                                @endif
                                                @if(!empty($customer->city))
                                                    &middot; {{ $customer->city }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="phone-container-{{ $customer->id }}">
                                                @if($isUnlocked)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 fw-bold fs-7">
                                                        <i class="fas fa-phone-alt me-1"></i> {{ $customer->phone }}
                                                    </span>
                                                @else
                                                    <span class="text-dark fw-bold">{{ $maskedPhone }}</span>
                                                    <a href="javascript:void(0);" class="unlock-phone-btn ms-2 text-primary fw-bold text-decoration-none small" data-id="{{ $customer->id }}">
                                                        <i class="fas fa-eye me-0.5"></i> Show
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {!! $statusTag !!}
                                            @if(!empty($item['was_reassigned']))
                                                {{-- reassigned to this person, keeping its follow-up --}}
                                                <span class="badge bg-light text-secondary border px-1.5 py-0.5 ms-1" style="font-size: 0.7rem;"><i class="fas fa-random me-1 opacity-75"></i>Reassigned</span>
                                            @endif
                                            @if($history && $history->calling_status)
                                                <div class="text-muted small mt-1 fw-medium">
                                                    @if($type === 'reassigned')
                                                        <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 0.7rem;"><i class="fas fa-history me-1 opacity-75"></i>Last: {{ $history->calling_status->name }}</span>
                                                    @else
                                                        {{ $history->calling_status->name }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($history && $history->date_required)
                                                <div class="fw-bold {{ $type === 'overdue' ? 'text-danger' : 'text-dark' }}">
                                                    <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($history->date_required)->format('d M, Y') }}
                                                </div>
                                                @if(!empty($item['called_today']) && !empty($item['last_called_at']))
                                                    <div class="text-muted small mt-0.5" style="font-size: 0.72rem;">
                                                        <i class="fas fa-redo-alt text-warning me-0.5"></i> Call again (Last: {{ \Carbon\Carbon::parse($item['last_called_at'])->format('h:i A') }})
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark small">{{ $assignmentsLookup[$customer->id]->assigner->name ?? 'System' }}</div>
                                            @if(isset($assignmentsLookup[$customer->id]->updated_at))
                                                <div class="text-muted small" style="font-size: 0.72rem;">
                                                    <i class="far fa-clock me-0.5"></i> {{ \Carbon\Carbon::parse($assignmentsLookup[$customer->id]->updated_at)->format('d M, Y h:i A') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                @if(isset($assignmentsLookup[$customer->id]) && ($assignmentsLookup[$customer->id]->assigned_by == auth()->id() || ($isTopLevel ?? false) || auth()->user()->is_admin || auth()->user()->hasRole('superadmin') || auth()->user()->can('customer-edit')))
                                                    <button type="button" class="btn btn-reassign-action open-reassign-modal" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" title="Reassign Lead">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-call-action open-calling-modal call-btn-{{ $customer->id }}" 
                                                    data-id="{{ $customer->id }}" 
                                                    data-name="{{ $customer->name }}" 
                                                    data-phone="{{ $isUnlocked ? $customer->phone : $maskedPhone }}" 
                                                    data-category="{{ $customer->category_id }}" 
                                                    data-lead-quality="{{ $customer->lead_quality_id }}">
                                                    <i class="fas fa-phone-alt"></i> Call Student
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
@endforeach
