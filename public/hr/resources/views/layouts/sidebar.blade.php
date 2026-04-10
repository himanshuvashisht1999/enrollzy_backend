<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">{!! organization_name() !!}</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Dashboard Link -->
    <li class="nav-item {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">


    @if(auth()->user()->role == "superadmin")
    <!-- Dashboard Link -->
    <li class="nav-item {{ Request::routeIs('admin.organization.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.organization.index') }}">
            <i class="fas fa-fw fa-building"></i>
            <span>Organizations</span>
        </a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    @endif



    <!-- Heading: HR & Management -->
    {{-- @if (auth()->user()->can('department-browse') || auth()->user()->can('designation-browse') || auth()->user()->can('staff-browse') || auth()->user()->can('roles-browse')) --}}
    <hr class="sidebar-divider">
    <div class="sidebar-heading"> HR & Management </div>
    <li class="nav-item">
        <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#hr_management"
            aria-expanded="true" aria-controls="hr_management">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Leaves</span>
        </a>
        <div id="hr_management"
            class="collapse {{ Request::is(['admin/leavePolicy*','admin/leaveSetting*', 'admin/holidays*', 'admin/leaves*']) ? 'show' : '' }}"
            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                @can('leaves-setting-browse')
                <a class="collapse-item {{ Request::is('admin/leaveSetting*') ? 'active' : '' }}"
                    href="{{ route('admin.leaveSetting.index') }}">Leaves Setting</a>
                @endcan
                @can('leaves-setting-browse')
                <a class="collapse-item {{ Request::is('admin/leavePolicy*') ? 'active' : '' }}"
                    href="{{ route('admin.leavePolicy.index') }}">Leaves Policies</a>
                @endcan
                @can('holiday-browse')
                <a class="collapse-item {{ Request::is('admin/holidays*') ? 'active' : '' }}"
                    href="{{ route('admin.holidays.index') }}">Holidays </a>

                @endcan
                @can('leaves-browse')
                <a class="collapse-item {{ Route::is('admin.leaves*') ? 'active' : '' }}"
                    href="{{ route('admin.leaves.index') }}">Leaves</a>
                @endcan

            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#staff_management"
            aria-expanded="true" aria-controls="staff_management">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Staff </span>
        </a>
        <div id="staff_management"
            class="collapse {{ Request::is(['admin/department*', 'admin/designation*', 'admin/staff*', 'admin/roles*']) ? 'show' : '' }}"
            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                @can('department-browse')
                <a class="collapse-item {{ Route::is('admin.department.index') ? 'active' : '' }}"
                    href="{{ route('admin.department.index') }}">Department</a>
                @endcan
                @can('designation-browse')
                <a class="collapse-item {{ Route::is('admin.designation.index') ? 'active' : '' }}"
                    href="{{ route('admin.designation.index') }}">Designation</a>
                @endcan
                @can('staff-browse')
                <a class="collapse-item {{ Route::is('admin.staff.index') ? 'active' : '' }}"
                    href="{{ route('admin.staff.index') }}">Staff</a>
                @endcan
                @can('roles-browse')
                <a class="collapse-item {{ Route::is('admin.roles.index') ? 'active' : '' }}"
                    href="{{ route('admin.roles.index') }}">Roles & Permission</a>
                @endcan
                @can('roles-browse')
                <a class="collapse-item {{ Route::is('admin.banks.index') ? 'active' : '' }}"
                    href="{{ route('admin.banks.index') }}">Bank Acccounts</a>
                @endcan
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#salary_management"
            aria-expanded="true" aria-controls="salary_management">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Salary </span>
        </a>
        <div id="salary_management"
            class="collapse {{ Request::is(['admin/attendance*', 'admin/advance*', 'admin/payroll*', 'admin/payOut*']) ? 'show' : '' }}"
            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">


                @can('attandance-browse')
                <a class="collapse-item {{ Route::is('admin.attendance.index') ? 'active' : '' }}"
                    href="{{ route('admin.attendance.index') }}">Attendance</a>
                @endcan
                @can('advancepay-browse')
                <a class="collapse-item {{ Route::is('admin.advance.index') ? 'active' : '' }}"
                    href="{{ route('admin.advance.index') }}">Advance Pay</a>
                @endcan
                @can('payroll-browse')
                <a class="collapse-item {{ Route::is('admin.payroll.index') ? 'active' : '' }}"
                    href="{{ route('admin.payroll.index') }}">Generate PayRoll</a>
                @endcan
                @can('payout-browse')
                <a class="collapse-item {{ Route::is('admin.payOut.index') ? 'active' : '' }}"
                    href="{{ route('admin.payOut.index') }}">View Payout</a>
                @endcan
            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#whatsapptemplate"
            aria-expanded="true" aria-controls="comp_ticket">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Whatsapp Template</span>
        </a>
        <div id="whatsapptemplate" class="collapse {{ Request::is(['admin/whatsapp_template*']) ? 'show' : '' }}"
            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Whatsapp Template:</h6>
                <a class="collapse-item {{ Route::is('admin.whatsapp_template.index') ? 'active' : '' }}"
                    href="{{ route('admin.whatsapp_template.index') }}">Templates</a>
                <a class="collapse-item {{ Route::is('admin.whatsapp_template.report') ? 'active' : '' }}"
                    href="{{ route('admin.whatsapp_template.report') }}">Report</a>

                    
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading"> Project & Tasks </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#project_management"
            aria-expanded="true" aria-controls="project_management">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Project & Tasks </span>
        </a>
        <div id="project_management"
            class="collapse {{ Request::is(['admin/leadSource*', 'admin/client*', 'admin/projectCategory*', 'admin/projects*', 'admin/milestones*', 'admin/task*']) ? 'show' : '' }}"
            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Projects & Tasks:</h6>
                @can('roles-browse')
                <a class="collapse-item" href="javascript:;">Notification Email</a>
                @endcan
                @can('lead-browse')
                <a class="collapse-item {{ Route::is('admin.leadSource.index') ? 'active' : '' }}"
                    href="{{ route('admin.leadSource.index') }}">Lead Source</a>
                @endcan
                @can('project-browse')
                <a class="collapse-item {{ Route::is('admin.client.index') ? 'active' : '' }}"
                    href="{{ route('admin.client.index') }}">Project Users</a>
                <a class="collapse-item {{ Route::is('admin.projectCategory.index') ? 'active' : '' }}"
                    href="{{ route('admin.projectCategory.index') }}">Project Category</a>
                <a class="collapse-item {{ Route::is('admin.projects.index') ? 'active' : '' }}"
                    href="{{ route('admin.projects.index') }}">Project List</a>
                @endcan
                @can('milestone-browse')
                <a class="collapse-item {{ Route::is('admin.milestones.index') ? 'active' : '' }}"
                    href="{{ route('admin.milestones.index') }}">All Milestones</a>
                @endcan
                @can('task-browse')
                <a class="collapse-item {{ Route::is('admin.task.index') ? 'active' : '' }}"
                    href="{{ route('admin.task.index') }}">Tasks</a>
                @endcan
                
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#customer_management"
            aria-expanded="true" aria-controls="customer_management">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Customers </span>
        </a>
        <div id="customer_management"
            class="collapse {{ Request::is(['admin/customer*', 'admin/customer_category*', 'admin/institute*']) ? 'show' : '' }}"
            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">All Customer Param:</h6>
                @can('customer-browse')
                <a class="collapse-item {{ Route::is('admin.customer.list') ? 'active' : '' }}"
                    href="{{ route('admin.customer.list') }}">Customers list</a>
                @endcan
                @can('customer-category-browse')
                <a class="collapse-item {{ Route::is('admin.customer_category.list') ? 'active' : '' }}"
                    href="{{ route('admin.customer_category.list') }}">Customers Category</a>
                @endcan
                @can('customer-browse')
                <a class="collapse-item {{ Route::is('admin.customer_field.list') ? 'active' : '' }}"
                    href="{{ route('admin.customer_field.list') }}">Customer Fields</a>
                @endcan
                @can('institute-browse')
                <a class="collapse-item {{ Route::is('admin.institute.list') ? 'active' : '' }}"
                    href="{{ route('admin.institute.list') }}">Institutes</a>
                @endcan
            </div>
        </div>
    </li>

<li class="nav-item">
    <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#student_management"
        aria-expanded="true" aria-controls="student_management">
        <i class="fas fa-fw fa-wrench"></i>
        <span>Students </span>
    </a>
    <div id="student_management"
        class="collapse {{ Request::is(['admin/customer*', 'admin/customer_category*','admin/call_action*','admin/call_status*','admin/calling*']) ? 'show' : '' }}"
        aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">All Student Param:</h6>
                @can('customer-browse')
                <a class="collapse-item {{ Route::is('admin.customer.list') ? 'active' : '' }}"
                    href="{{ route('admin.customer.list') }}">Classes</a>
                @endcan
                @can('customer-category-browse')
                <a class="collapse-item {{ Route::is('admin.customer_category.list') ? 'active' : '' }}"
                    href="{{ route('admin.customer_category.list') }}">Students</a>
                @endcan
                @can('calling-browse')
                <a class="collapse-item {{ Route::is('admin.call_status.index') ? 'active' : '' }}"
                    href="{{ route('admin.call_status.index') }}">Calling Status</a>
                <a class="collapse-item {{ Route::is('admin.call_action.index') ? 'active' : '' }}"
                    href="{{ route('admin.call_action.index') }}">Calling Action</a>
                <a class="collapse-item {{ Route::is('admin.calling.index') ? 'active' : '' }}"
                    href="{{ route('admin.calling.index') }}">Calling Module</a>
                <a class="collapse-item {{ Route::is('admin.calling.history') ? 'active' : '' }}"
                    href="{{ route('admin.calling.history') }}">Calling History</a>
                @endcan
        </div>
    </div>
</li>

    <!-- Projects & Tasks Menu -->
    <hr class="sidebar-divider">
    <!-- Sidebar Toggle Button -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <!-- Sidebar Message -->

</ul>
