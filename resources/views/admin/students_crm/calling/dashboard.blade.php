@extends('admin.layouts.master')

@section('title', 'Calling Dashboard')

@push('css')
<style>
    .metric-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
        background: #fff;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }
    .metric-title {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 600;
    }
    .metric-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1a1d20;
    }
    
    .queue-list {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
    }
    .queue-item {
        border-left: 4px solid transparent;
        transition: background-color 0.2s;
    }
    .queue-item:hover {
        background-color: #f8f9fa;
    }
    .queue-item.type-overdue { border-left-color: #dc3545; }
    .queue-item.type-due_today { border-left-color: #ffc107; }
    .queue-item.type-new { border-left-color: #0d6efd; }
    
    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .text-overdue { color: #dc3545; }
    .text-due_today { color: #ffc107; }
    .text-new { color: #0d6efd; }
    
    .lead-info-small {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="background-color: #f4f6fb; min-height: 100vh;">
    <!-- Date Filter Form -->
    <form method="GET" action="{{ route('admin.students-crm.calling-dashboard.index') }}" class="mb-4 bg-white p-3 rounded-3 shadow-sm border-0">
        <div class="row align-items-end g-3">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary rounded-pill px-4">Filter</button>
                <a href="{{ route('admin.students-crm.calling-dashboard.index') }}" class="btn btn-light rounded-pill px-4 ms-2">Today</a>
            </div>
        </div>
    </form>

    <!-- Top Metrics -->
    <div class="row g-3 mb-5">
        <div class="col">
            <div class="card metric-card shadow-sm h-100 p-3">
                <div class="metric-value">{{ $leadsAssignedTodayCount }}</div>
                <div class="metric-title">Leads assigned (in range)</div>
            </div>
        </div>
        <div class="col">
            <div class="card metric-card shadow-sm h-100 p-3">
                <div class="metric-value">{{ $pendingInQueueCount }}</div>
                <div class="metric-title">Pending in queue</div>
            </div>
        </div>
        <div class="col">
            <div class="card metric-card shadow-sm h-100 p-3">
                <div class="metric-value">{{ $followUpsDueTodayCount }}</div>
                <div class="metric-title">Follow-ups due (in range)</div>
            </div>
        </div>
        <div class="col">
            <div class="card metric-card shadow-sm h-100 p-3">
                <div class="d-flex align-items-baseline gap-2">
                    <div class="metric-value">{{ $admissionsThisMonthCount }}/{{ $admissionsTarget }}</div>
                </div>
                <div class="metric-title mb-2">Admissions (for Selected Month)</div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $targetProgress }}%"></div>
                </div>
                @if($admissionsTarget > $admissionsThisMonthCount)
                    <div class="text-muted mt-1" style="font-size: 0.75rem;">{{ $admissionsTarget - $admissionsThisMonthCount }} more to hit target</div>
                @else
                    <div class="text-success mt-1" style="font-size: 0.75rem;">Target achieved!</div>
                @endif
            </div>
        </div>
        <div class="col">
            <div class="card metric-card shadow-sm h-100 p-3">
                <div class="metric-value">{{ $targetProgress }}%</div>
                <div class="metric-title">Target progress</div>
            </div>
        </div>
    </div>

    <!-- Team Metrics -->
    @if($hasSubordinates)
    <div class="mb-5">
        <h5 class="fw-bold mb-3 border-bottom pb-2">Team Performance <small class="text-muted fw-normal ms-2">(Leads delegated to your subordinates in this date range)</small></h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card metric-card shadow-sm h-100 p-3 bg-primary bg-opacity-10 border-primary border-opacity-25">
                    <div class="metric-value text-primary">{{ $teamLeadsDelegated }}</div>
                    <div class="metric-title text-primary">Leads Delegated to Team</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card metric-card shadow-sm h-100 p-3 bg-success bg-opacity-10 border-success border-opacity-25">
                    <div class="metric-value text-success">{{ $teamAdmissionsCount }}</div>
                    <div class="metric-title text-success">Team Admissions Achieved</div>
                </div>
            </div>
        </div>
        
        @if(count($teamMetrics) > 0)
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4">Subordinate Name</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4 text-center">Leads Assigned</th>
                                <th class="py-3 px-4 text-center">Worked On</th>
                                <th class="py-3 px-4 text-center">Pending (New)</th>
                                <th class="py-3 px-4 text-center">Follow-ups Due</th>
                                <th class="py-3 px-4 text-center text-success">Admissions Closed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teamMetrics as $sub)
                            <tr>
                                <td class="py-3 px-4 fw-bold">{{ $sub['name'] }}</td>
                                <td class="py-3 px-4 text-muted">{{ $sub['role'] }}</td>
                                <td class="py-3 px-4 text-center">{{ $sub['leads_assigned'] }}</td>
                                <td class="py-3 px-4 text-center">{{ $sub['leads_worked'] }}</td>
                                <td class="py-3 px-4 text-center text-danger">{{ $sub['leads_pending'] }}</td>
                                <td class="py-3 px-4 text-center text-warning">{{ $sub['followups_due'] }}</td>
                                <td class="py-3 px-4 text-center text-success fw-bold">{{ $sub['admissions'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Lead Queue -->
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h5 class="fw-bold mb-2">Your lead queue</h5>
            <div class="d-flex gap-3 align-items-center" style="font-size: 0.85rem;">
                <span><i class="fas fa-circle text-danger me-1" style="font-size: 8px;"></i> Overdue follow-up</span>
                <span><i class="fas fa-circle text-warning me-1" style="font-size: 8px;"></i> Due (in range)</span>
                <span><i class="fas fa-circle text-primary me-1" style="font-size: 8px;"></i> New, unattempted</span>
            </div>
        </div>
        <div class="text-muted" style="font-size: 0.85rem;">
            Follow-ups always surface first Â· sorted by most overdue
        </div>
    </div>

    <div class="queue-list shadow-sm border">
        @forelse($queue as $item)
            @php 
                $customer = $item['customer'];
                $history = $item['history'];
                $type = $item['type'];
                
                $typeClass = '';
                $typeLabel = '';
                if ($type === 'overdue') {
                    $typeClass = 'type-overdue';
                    $typeLabel = '<span class="status-badge text-overdue bg-danger bg-opacity-10 px-2 py-1 rounded">OVERDUE FOLLOW-UP</span>';
                } elseif ($type === 'due_today') {
                    $typeClass = 'type-due_today';
                    $typeLabel = '<span class="status-badge text-due_today bg-warning bg-opacity-10 px-2 py-1 rounded">FOLLOW-UP DUE</span>';
                } else {
                    $typeClass = 'type-new';
                    $typeLabel = '<span class="status-badge text-new bg-primary bg-opacity-10 px-2 py-1 rounded">NEW UNATTEMPTED</span>';
                }
            @endphp
            
            <div class="queue-item {{ $typeClass }} p-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3 w-50">
                    <div style="width: 140px;">
                        {!! $typeLabel !!}
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">{{ $customer->name }}</h6>
                        <div class="lead-info-small">
                            {{ $customer->interested_in_course ?? 'N/A' }} - {{ $customer->city ?? 'Unknown' }}
                            @if($history && $history->date_required)
                                <br><i class="far fa-calendar-alt mt-1"></i> {{ \Carbon\Carbon::parse($history->date_required)->format('d M') }}
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-4">
                    @if($history && $history->calling_status)
                        <div class="text-muted small text-end" style="width: 200px;">
                            {{ $history->calling_status->name ?? 'No status' }}
                        </div>
                    @endif
                    
                    @php
                        $maskedPhone = substr($customer->phone, 0, 2) . 'XXX XX' . substr($customer->phone, -3);
                        $isUnlocked = (isset($unlocked_lead_id) && $unlocked_lead_id == $customer->id);
                    @endphp
                    <div class="text-muted small phone-container-{{ $customer->id }}">
                        @if($isUnlocked)
                            <i class="fas fa-phone me-1 text-success"></i> <span class="real-phone">{{ $customer->phone }}</span>
                        @else
                            <i class="fas fa-lock me-1 text-warning"></i> <span class="masked-phone">{{ $maskedPhone }}</span>
                            <a href="javascript:void(0);" class="unlock-phone-btn ms-2 text-primary" data-id="{{ $customer->id }}"><i class="fas fa-eye"></i> View</a>
                        @endif
                    </div>
                    
                    <button type="button" class="btn btn-dark rounded-pill px-4 open-calling-modal call-btn-{{ $customer->id }}" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $isUnlocked ? $customer->phone : $maskedPhone }}" data-category="{{ $customer->category_id }}" style="font-weight: 500;">
                        Call & Update
                    </button>
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">
                <i class="fas fa-check-circle fs-1 mb-3 text-success"></i>
                <h5>You're all caught up!</h5>
                <p>There are no leads pending in your queue.</p>
            </div>
        @endforelse
    </div>
</div>
> <!-- Update Calling Status Modal -->
  <div class="modal fade" id="callModal">
      <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content border-0 rounded-4 shadow-lg">
              <div class="modal-header border-0 pb-0">
                  <h5 class="fw-bold">Update Calling Status</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form id="callForm" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="group_id" value="{{ request('group', 1) }}">
                  <input type="hidden" id="customer_id" name="customer_id">
                  <input type="hidden" id="user_phone" name="user_phone">
                  <input type="hidden" id="category_val" name="category">
                  <div class="modal-body">
                      <div class="row g-3">
                          <div class="col-lg-6">
                              <label class="form-label small fw-bold">Name</label>
                              <input type="text" class="form-control rounded-3" name="name" id="user_name" readonly>
                          </div>
                          <div class="col-lg-6">
                              <label class="form-label small fw-bold">Call Status <span 
class="text-danger">*</span></label>
                              <select name="status_id" class="form-select rounded-3" id="status_id" required>
                                  <option value="" selected disabled>Select</option>
                                  @foreach($statuses as $status)
                                      <option value="{{ $status->id }}" data-action="{{ $status->calling_action_id }}" 
data-more-details="{{ $status->is_more_details }}" data-date-require="{{ $status->date_require }}">{{ $status->name 
}}</option>
                                  @endforeach
                              </select>
                          </div>
                          
                          <!-- More Details Container -->
                          <div id="more-details-container" class="col-12" style="display:none; margin-top: 0px;">
                              <div class="row g-3">
                                  <div class="col-lg-4">
                                      <label class="form-label small fw-bold">Program Level</label>
                                      <select name="program_level_id" id="program_level_id" class="form-select 
rounded-3 custom-select2">
                                          <option value="">Select or Type Program Level</option>
                                          <option value="Not decided yet">Not decided yet</option>
                                          @if(isset($program_levels))
                                              @foreach($program_levels as $pl)
                                                  <option value="{{ $pl->id }}">{{ $pl->title }}</option>
                                              @endforeach
                                          @endif
                                      </select>
                                  </div>
                                  <div class="col-lg-4" id="school_type_container" style="display:none;">
                                      <label class="form-label small fw-bold">School Type</label>
                                      <select name="school_type" id="school_type" class="form-select rounded-3 
custom-select2">
                                          <option value="">Select or Type School Type</option>
                                          @if(isset($school_types))
                                              @foreach($school_types as $st)
                                                  <option value="{{ $st->id }}">{{ $st->title }}</option>
                                              @endforeach
                                          @endif
                                      </select>
                                  </div>
                                  <div class="col-lg-4" id="course_container">
                                      <label class="form-label small fw-bold" id="course_label">Course</label>
                                      <select name="course_input" id="course_input" class="form-select rounded-3 
custom-select2">
                                          <option value="">Select or Type Course</option>
                                          <option value="Not decided yet">Not decided yet</option>
                                          @foreach($courses as $course)
                                              <option value="{{ $course->id }}">{{ $course->name }}</option>
                                          @endforeach
                                      </select>
                                  </div>
                                  <div class="col-lg-4" id="course_type_container">
                                      <label class="form-label small fw-bold">Program Mode</label>
                                      <select name="course_type" id="course_type" class="form-select rounded-3 
custom-select2">
                                          <option value="">Select or Type Program Mode</option>
                                          <option value="Not decided yet">Not decided yet</option>
                                          @if(isset($program_types))
                                              @foreach($program_types as $pt)
                                                  <option value="{{ $pt->title }}" data-db-id="{{ $pt->id }}">{{ 
$pt->title }}</option>
                                              @endforeach
                                          @endif
                                      </select>
                                  </div>
                                  <div class="col-lg-6" id="university_container">
                                      <label class="form-label small fw-bold" id="university_label">University / 
Organization</label>
                                      <select name="university_input" id="university_input" class="form-select 
rounded-3 custom-select2">
                                          <option value="">Select or Type University</option>
                                          <option value="Not decided yet">Not decided yet</option>
                                          @foreach($universities as $uni)
                                              @php
                                                  $types = [];
                                                  $orgType = is_array($uni->campus_type_new_id) ? 
$uni->campus_type_new_id : json_decode($uni->campus_type_new_id, true) ?? [$uni->campus_type_new_id];
                                                  if(is_array($orgType)) {
                                                      $types = array_merge($types, $orgType);
                                                  }
                                                  if ($uni->campuses) {
                                                      foreach($uni->campuses as $campus) {
                                                          $campType = is_array($campus->campus_type_new_id) ? 
$campus->campus_type_new_id : json_decode($campus->campus_type_new_id, true) ?? [$campus->campus_type_new_id];
                                                          if(is_array($campType)) {
                                                              $types = array_merge($types, $campType);
                                                          }
                                                      }
                                                  }
                                                  // Clean up array
                                                  $types = array_values(array_unique(array_filter($types)));
                                              @endphp
                                              <option value="{{ $uni->id }}" data-type-id="{{ 
$uni->organisation_type_id }}" data-school-type-id="{{ json_encode($types) }}">{{ $uni->name }}</option>
                                          @endforeach
                                      </select>
                                  </div>
                                  <div class="col-lg-6">
                                      <label class="form-label small fw-bold">Session</label>
                                      <select name="session" id="session_input" class="form-select rounded-3 
custom-select2">
                                          <option value="">Select Session</option>
                                          @if(isset($sessions))
                                              @foreach($sessions as $session)
                                                  <option value="{{ $session->id }}">{{ $session->name }}</option>
                                              @endforeach
                                          @endif
                                      </select>
                                  </div>
                              </div>
                          </div>
  
                          <div class="col-lg-6" id="date-field" style="display:none;">
                              <label class="form-label small fw-bold">Reminder Date</label>
                              <input type="date" name="next_call_date" class="form-control rounded-3" id="call_date">
                          </div>
  
                          <div class="col-lg-6">
                              <label class="form-label small fw-bold">Action Taken</label>
                              <select name="action_id" id="action_id" class="form-select rounded-3">
                                  <option value="">Select Action</option>
                                  @foreach($actions as $action)
                                      <option value="{{ $action->id }}">{{ $action->name }}</option>
                                  @endforeach
                              </select>
                          </div>
                          
                          <!-- Video Meeting Container -->
                          <div id="video-meeting-container" class="col-12" style="display:none; margin-top: 0px;">
                              <div class="row g-3">
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Meeting Date</label>
                                      <input type="date" name="meeting_date" id="meeting_date" class="form-control 
rounded-3">
                                  </div>
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Time Slot</label>
                                      <input type="time" name="time_slot" id="time_slot" class="form-control 
rounded-3">
                                  </div>
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Google Meeting Link</label>
                                      <input type="url" name="meeting_link" id="meeting_link" class="form-control 
rounded-3" placeholder="https://meet.google.com/...">
                                  </div>
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Assign Lead to Staff</label>
                                      <select name="assign_to_staff_id" id="assign_to_staff_id" class="form-select 
rounded-3 custom-select2">
                                          <option value="">Select Staff</option>
                                          @foreach($staffs as $staff)
                                              <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                          @endforeach
                                      </select>
                                  </div>
                              </div>
                          </div>
                          
                          <div class="col-12">
                              <label class="form-label small fw-bold">Comments</label>
                              <textarea id="message" name="remark" class="form-control rounded-3" rows="3" 
placeholder="Add Comments Here..."></textarea>
                          </div>
  
                          <div class="col-12 mt-3">
                              <div class="form-check">
                                  <input class="form-check-input" type="checkbox" id="is_whatsapp_message" 
name="is_whatsapp_message" value="1">
                                  <label class="form-check-label fw-bold" for="is_whatsapp_message">
                                      Want to send whatsapp message?
                                  </label>
                              </div>
                          </div>
  
                          <div id="whatsapp_fields" class="col-12" style="display:none;">
                              <div class="row g-3 mt-1">
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Template</label>
                                      <select name="whatsapp_template_id" class="form-select rounded-3" 
id="whatsapp_template_id">
                                          <option value="">Select</option>
                                          @foreach($templates as $template)
                                              <option value="{{ $template->id }}" data-caption="{{ $template->caption 
}}" data-message="{{ $template->message }}">{{ $template->name }}</option>
                                          @endforeach
                                      </select>
                                  </div>
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Caption</label>
                                      <input type="text" class="form-control rounded-3" name="caption" id="caption">
                                  </div>
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Image</label>
                                      <input type="file" class="form-control rounded-3" name="image_whatsapp" 
accept=".jpg, .jpeg, .png">
                                      <small class="text-muted">Upload an image (jpg, jpeg, png) max 2MB.</small>
                                  </div>
                                  <div class="col-lg-3">
                                      <label class="form-label small fw-bold">Start Time</label>
                                      <input type="datetime-local" class="form-control rounded-3" name="start_time" 
value="{{ now()->format('Y-m-d\TH:i') }}">
                                  </div>     
                                  <div class="col-12">
                                      <label class="form-label small fw-bold">Message</label>
                                      <textarea name="whatsapp_message" class="form-control rounded-3" 
id="message-editor" placeholder="Enter message"></textarea>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer border-0">
                      <button type="button" class="btn btn-light rounded-pill px-4" 
data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary rounded-pill px-5">Save Log</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
@endsection
  

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        $('#restartBtn').on('click', function() {
            let cat = $('#categoryFilter').val();
            let group = '{{ request('group', 1) }}';
            let baseUrl = '{{ route('admin.students-crm.calling-module.restart') }}';
            
            if(!cat) {
                Swal.fire('Warning', 'Please select a category first before clicking Re-Start.', 'warning');
                return;
            }
            
            var params = new URLSearchParams();
            params.set('group', group);
            params.set('category', cat);
            
            window.location.href = baseUrl + '?' + params.toString();
        });

        $(document).on('click', '.unlock-phone-btn', function() {
            let id = $(this).data('id');
            let btn = $(this);
            
            $.ajax({
                url: "{{ route('admin.students-crm.calling-dashboard.unlock') }}",
                type: 'POST',
                data: {
                    customer_id: id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.status == 1) {
                        $('.phone-container-' + id).html('<i class="fas fa-phone me-1 text-success"></i> <span class="real-phone">' + res.phone + '</span>');
                        $('.call-btn-' + id).attr('data-phone', res.phone);
                        $('.call-btn-' + id).data('phone', res.phone); // update jQuery data too
                    } else {
                        Swal.fire('Locked', res.message, 'warning');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });

        $(document).on('click', '.open-calling-modal', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let phone = $(this).data('phone');
            let cat = $(this).data('category');

            $('#customer_id').val(id);
            $('#user_name').val(name);
            $('#user_phone').val(phone);
            $('#category_val').val(cat);
            $('#callModal').modal('show');
        });

        $('#callForm').on('submit', function(e) {
            e.preventDefault();
            
            // Sync CKEditor
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].updateElement();
            }

            $.ajax({
                url: "{{ route('admin.students-crm.calling-module.store') }}",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.status == 1) {
                        $('#callModal').modal('hide');
                        $('#callForm')[0].reset();
                        $('#university_input').val(null).trigger('change');
                        $('#course_input').val(null).trigger('change');
                        $('#assign_to_staff_id').val(null).trigger('change');
                        $('#more-details-container').hide();
                        $('#video-meeting-container').hide();
                        $('#date-field').hide();
                        $('#call_date').prop('required', false);
                        Swal.fire('Interactions Logged', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });

        // Group Radio
        const radios = document.querySelectorAll('input[type="radio"][name="group"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                const selectedValue = this.value;
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?group=" + selectedValue;
                window.location.href = newUrl;
            });
        });

        // Switches Labels
        $('#toggleUserWithoutStatus').on('change', function() {
            $('#toggleLabel').text(this.checked ? 'Yes' : 'No');
        });
        $('#toggleSequence').on('change', function() {
            $('#sequenceLabel').text(this.checked ? 'ON (Pending only)' : 'OFF (Normal)');
        });

        // Whatsapp Toggle
        $('#is_whatsapp_message').on('change', function() {
            if(this.checked) {
                $('#whatsapp_fields').show();
            } else {
                $('#whatsapp_fields').hide();
                $('#caption').val('');
                if (CKEDITOR.instances['message-editor']) {
                    CKEDITOR.instances['message-editor'].setData('');
                }
            }
        });

        // CKEditor Init
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('message-editor', {});
        }

        $('#whatsapp_template_id').on('change', function() {
            let selected = $(this).find('option:selected');
            let caption = selected.data('caption') || '';
            let msg = selected.data('message') || '';
            
            $('#caption').val(caption);
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].setData(msg);
            }
        });

        $('#status_id').on('change', function() {
            let selected = $(this).find('option:selected');
            let actionId = selected.data('action');
            let moreDetails = selected.data('more-details');
            let dateRequire = selected.data('date-require');
            
            if(actionId) {
                $('#action_id').val(actionId).trigger('change');
            } else {
                $('#action_id').val('').trigger('change');
            }
            
            if(moreDetails === 'yes') {
                $('#more-details-container').show();
            } else {
                $('#more-details-container').hide();
            }
            
            if(dateRequire === 'yes') {
                $('#date-field').show();
                $('#call_date').prop('required', true);
            } else {
                $('#date-field').hide();
                $('#call_date').prop('required', false);
            }
        });
        
        $('#university_input').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type University",
            allowClear: true,
            width: '100%'
        });

        $('#course_input').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Course",
            allowClear: true,
            width: '100%'
        });

        $('#program_level_id').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Program Level",
            allowClear: true,
            width: '100%'
        });

        $('#course_type').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Course Type",
            allowClear: true,
            width: '100%'
        });

        $('#assign_to_staff_id').select2({
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select Staff",
            allowClear: true,
            width: '100%'
        });

        $('#action_id').on('change', function() {
            let actionText = $(this).find('option:selected').text().trim().toLowerCase();
            if(actionText === 'arrange video meeting') {
                $('#video-meeting-container').show();
            } else {
                $('#video-meeting-container').hide();
            }
        });
        
        let allUniversities = [];
        let allCourseTypes = [];
        let allCourses = [];
        let courseProgramTypes = @json($course_program_types ?? []);
        
        $(document).ready(function() {
            $('#university_input option').each(function() {
                let stid = $(this).attr('data-school-type-id');
                try {
                    stid = JSON.parse(stid);
                } catch(e) {}
                allUniversities.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    typeId: $(this).data('type-id'),
                    schoolTypeId: stid
                });
            });
            
            $('#course_type option').each(function() {
                allCourseTypes.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    dbId: $(this).data('db-id')
                });
            });

            $('#course_input option').each(function() {
                allCourses.push({
                    id: $(this).val(),
                    text: $(this).text()
                });
            });
        });

        $('#school_type').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type School Type",
            allowClear: true,
            width: '100%'
        });

        $('#program_level_id').on('change', function() {
            let levelId = $(this).val();
            let selectedText = $(this).find('option:selected').text().trim().toLowerCase();
            
            let universitySelect = $('#university_input');
            universitySelect.empty();

            if (selectedText === 'school') {
                $('#school_type_container').show();
                $('#course_label').text('Choose Class');
                $('#course_type_container').hide();
                $('#university_label').text('School Name');
                
                $('#school_type').val('');
                // Let the school_type change event handle populating universitySelect
                setTimeout(function() {
                    $('#school_type').trigger('change');
                }, 10);
            } else if (selectedText === 'competetive coaching' || selectedText === 'competitive coaching') {
                $('#school_type_container').hide();
                $('#course_label').text('Course');
                $('#course_type_container').show();
                $('#university_label').text('Choose institute');
                
                allUniversities.forEach(function(u) {
                    if (!u.id || u.id === 'Not decided yet' || u.typeId == 3) {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    }
                });
            } else {
                $('#school_type_container').hide();
                $('#course_label').text('Course');
                $('#course_type_container').show();
                $('#university_label').text('University / Organization');
                
                allUniversities.forEach(function(u) {
                    if (u.typeId != 4 || !u.id || u.id === 'Not decided yet') {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    }
                });
            }
            universitySelect.trigger('change');

            let courseSelect = $('#course_input');
            courseSelect.html('<option value="">Loading...</option>').trigger('change');
            
            $.ajax({
                url: '{{ route("admin.students-crm.calling-module.get-courses") }}',
                type: 'GET',
                data: { program_level_id: levelId },
                success: function(res) {
                    let html = '<option value="">Select or Type Course</option>';
                    html += '<option value="Not decided yet">Not decided yet</option>';
                    if(res && res.length > 0) {
                        res.forEach(c => {
                            html += `<option value="${c.id}">${c.name}</option>`;
                        });
                    } else {
                        allCourses.forEach(function(c) {
                            if (c.id && c.id !== 'Not decided yet') {
                                html += `<option value="${c.id}">${c.text}</option>`;
                            }
                        });
                    }
                    courseSelect.html(html).trigger('change');
                },
                error: function() {
                    courseSelect.html('<option value="">Select or Type Course</option><option value="Not decided yet">Not decided yet</option>').trigger('change');
                }
            });
        });

        $('#school_type').on('change', function() {
            let schoolTypeId = $(this).val();
            let universitySelect = $('#university_input');
            let currentVal = universitySelect.val();
            universitySelect.empty();
            
            allUniversities.forEach(function(u) {
                if (!u.id || u.id === 'Not decided yet' || u.typeId == 4) {
                    if (!schoolTypeId || !u.id || u.id === 'Not decided yet') {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    } else {
                        let sTypes = Array.isArray(u.schoolTypeId) ? u.schoolTypeId.map(String) : (u.schoolTypeId ? [String(u.schoolTypeId)] : []);
                        if (sTypes.includes(String(schoolTypeId))) {
                            let option = new Option(u.text, u.id, false, false);
                            $(option).attr('data-type-id', u.typeId);
                            universitySelect.append(option);
                        }
                    }
                }
            });
            universitySelect.val(currentVal).trigger('change');
        });

        $('#course_input').on('change', function() {
            let courseId = $(this).val();
            let programLevelText = $('#program_level_id').find('option:selected').text().trim().toLowerCase();
            
            let courseTypeSelect = $('#course_type');
            
            if (programLevelText === 'competetive coaching' || programLevelText === 'competitive coaching') {
                courseTypeSelect.empty();
                
                let option1 = new Option('Select or Type Program Mode', '', false, false);
                let option2 = new Option('Not decided yet', 'Not decided yet', false, false);
                courseTypeSelect.append(option1).append(option2);

                if (courseId && courseId !== 'Not decided yet') {
                    let allowedTypeIds = courseProgramTypes
                        .filter(cpt => cpt.course_id == courseId)
                        .map(cpt => parseInt(cpt.program_type_id));
                        
                    allCourseTypes.forEach(function(ct) {
                        if (ct.id && ct.id !== 'Not decided yet' && allowedTypeIds.includes(parseInt(ct.dbId))) {
                            let option = new Option(ct.text, ct.id, false, false);
                            $(option).attr('data-db-id', ct.dbId);
                            courseTypeSelect.append(option);
                        }
                    });
                }
                courseTypeSelect.trigger('change');
            } else {
                // Restore all course types if not coaching
                courseTypeSelect.empty();
                allCourseTypes.forEach(function(ct) {
                    let option = new Option(ct.text, ct.id, false, false);
                    if (ct.dbId) $(option).attr('data-db-id', ct.dbId);
                    courseTypeSelect.append(option);
                });
                courseTypeSelect.trigger('change');
            }
        });

        // Countries API
        const API_BASE = 'https://countriesnow.space/api/v0.1';
        loadCountries();

        $('#countryFilter').on('change', function () {
            const country = $(this).val();
            $('#stateFilter').html('<option value="">Select State</option>');
            $('#cityFilter').html('<option value="">Select City</option>');
            if (country) loadStates(country);
        });

        $('#stateFilter').on('change', function () {
            const country = $('#countryFilter').val();
            const state   = $(this).val();
            $('#cityFilter').html('<option value="">Select City</option>');
            if (country && state) loadCities(country, state);
        });

        function loadCountries() {
            $.get(API_BASE + '/countries', function(res){
                let html = '<option value="">Select Country</option>';
                res.data.forEach(c => {
                    html += `<option value="${c.country}">${c.country}</option>`;
                });
                $('#countryFilter').html(html);
            });
        }

        function loadStates(country) {
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/states',
                contentType: 'application/json',
                data: JSON.stringify({ country }),
                success: function(res){
                    let html = '<option value="">Select State</option>';
                    res.data.states.forEach(s => {
                        html += `<option value="${s.name}">${s.name}</option>`;
                    });
                    $('#stateFilter').html(html);
                }
            });
        }

        function loadCities(country, state) {
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/state/cities',
                contentType: 'application/json',
                data: JSON.stringify({ country, state }),
                success: function(res){
                    let html = '<option value="">Select City</option>';
                    res.data.forEach(city => {
                        html += `<option value="${city}">${city}</option>`;
                    });
                    $('#cityFilter').html(html);
                }
            });
        }
        // Date Filter Restrictions
        $('#start_date').on('change', function() {
            var startDateVal = $(this).val();
            if (startDateVal) {
                var parts = startDateVal.split('-');
                var year = parts[0];
                var month = parts[1];
                
                // new Date(year, monthIndex, 0) gives the last day of the previous month.
                // Since our 'month' string is 1-indexed (e.g. '07' for July), passing it as monthIndex (which is 0-indexed) 
                // means we are getting the last day of the month we want!
                var lastDay = new Date(year, parseInt(month), 0).getDate();
                
                var firstDayStr = year + "-" + month + "-01";
                var lastDayStr = year + "-" + month + "-" + lastDay;
                
                $('#end_date').attr('min', firstDayStr);
                $('#end_date').attr('max', lastDayStr);
                
                var currentEndDate = $('#end_date').val();
                if(currentEndDate < firstDayStr || currentEndDate > lastDayStr) {
                    $('#end_date').val(startDateVal);
                }
            } else {
                $('#end_date').removeAttr('min').removeAttr('max');
            }
        });
        
        // Trigger immediately on load to enforce constraints if values exist
        $('#start_date').trigger('change');
    });
</script>
@endpush

