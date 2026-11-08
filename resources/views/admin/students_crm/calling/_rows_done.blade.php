{{-- Calling dashboard, Completed Calls rows (also loaded by "See more": $offset = rows already shown) --}}
@foreach($rows as $hItem)
                                    @php 
                                        $customer = $hItem->customer;
                                        $maskedPhone = $customer ? substr($customer->phone, 0, 2) . 'XXXXX' . substr($customer->phone, -3) : '';
                                        $isUnlocked = ($customer && isset($unlocked_lead_id) && $unlocked_lead_id == $customer->id);
                                        // the lead has moved on since this call: who has it now (one owner per lead; the call stays in the caller's list)
                                        $owner = $customer ? ($historyAssignmentsLookup[$customer->id] ?? null) : null;
                                        $movedTo = ($owner && $owner->staff_id != $hItem->updated_by) ? ($owner->staff_id == auth()->id() ? 'you' : ($owner->staff->name ?? 'another staff member')) : null;
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
                                            <span class="status-tag tag-completed">
                                                <i class="fas fa-check-circle"></i> {{ $hItem->calling_status->name ?? 'Contacted' }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            {{ $hItem->staff->name ?? 'System' }}
                                            @if($movedTo)
                                                <div><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold mt-1" style="font-size: 0.7rem;" title="This lead has been reassigned since this call">
                                                    <i class="fas fa-share me-1"></i>Now with {{ $movedTo }}</span></div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark small">{{ $historyAssignmentsLookup[$customer->id]->assigner->name ?? '—' }}</div>
                                            @if(isset($historyAssignmentsLookup[$customer->id]->updated_at))
                                                <div class="text-muted small" style="font-size: 0.72rem;">
                                                    <i class="far fa-clock me-0.5"></i> {{ \Carbon\Carbon::parse($historyAssignmentsLookup[$customer->id]->updated_at)->format('d M, Y h:i A') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($hItem->created_at)->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                @if(isset($historyAssignmentsLookup[$customer->id]) && ($historyAssignmentsLookup[$customer->id]->assigned_by == auth()->id() || ($isTopLevel ?? false) || auth()->user()->is_admin || auth()->user()->hasRole('superadmin') || auth()->user()->can('customer-edit')))
                                                    <button type="button" class="btn btn-reassign-action open-reassign-modal" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" title="Reassign">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-call-action open-calling-modal call-btn-{{ $customer->id }}" 
                                                    data-id="{{ $customer->id }}" 
                                                    data-name="{{ $customer->name }}" 
                                                    data-phone="{{ $isUnlocked ? $customer->phone : $maskedPhone }}" 
                                                    data-category="{{ $customer->category_id }}" 
                                                    data-lead-quality="{{ $customer->lead_quality_id }}">
                                                    <i class="fas fa-phone-alt"></i> Call Again
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
@endforeach
