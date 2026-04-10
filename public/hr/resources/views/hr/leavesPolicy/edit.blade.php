@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
    <div class="container-fluid">
        <form id="updateLeaveTypeForm" method="POST" action="{{ route('admin.leavePolicy.update', encrypt($lSetting->id)) }}">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 form-group">
                                        <label for="name">Leave Type Name</label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $lSetting->name) }}"
                                            required>
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label for="department">Department</label>
                                        <select name="department[]" class="form-control" multiple id="departmentSelect">
                                            @foreach ($department as $depart)
                                                <option value="{{ $depart->id }}"
                                                    {{ in_array($depart->id, old('department', explode(',', $lSetting->department_ids))) ? 'selected' : '' }}>
                                                    {{ $depart->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label for="designation">Designation</label>
                                        <select name="designation[]" class="form-control" multiple id="designationSelect">
                                            @foreach ($designation as $designat)
                                                <option value="{{ $designat->id }}"
                                                    {{ in_array($designat->id, old('designation', explode(',', $lSetting->designation_ids))) ? 'selected' : '' }}>
                                                    {{ $designat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="role_for">Users</label>
                                        <select name="working_days[]" multiple id="workingDays" class="form-control">
                                            @foreach ($users as $designat)
                                                <option value="{{ $designat->id }}"
                                                    {{ in_array($designat->id, old('designation', explode(',', $lSetting->staff_ids))) ? 'selected' : '' }}>
                                                    {{ $designat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-lg-12">
                                        <label for="contentBoxID">Content</label>
                                        <textarea name="policy" id="contentBoxID" class="form-control">{{ $lSetting->policy }}</textarea>
                                    </div>
                                </div>
            </form>
            <div class="card-footer text-right">
                <a href="{{ route('admin.leaveSetting.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="updateLeaveTypeForm" class="btn btn-primary btn-sm">Update Type</a>
            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
    </div>
@endsection
@section('push_script')
    <script src="{{ URL::asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="{{ URL::asset('admin/js/tinymce/tinymce.min.js') }}"></script>
    <script>
        $('#featured').filemanager('image');
        $(document).ready(function() {
            $('#add_category').select2({
                theme: 'bootstrap4',
            });
        });
        $(document).ready(function() {
            const useDarkMode = false; // Always set to false to force light mode
            const isSmallScreen = window.matchMedia('(max-width: 1023.5px)').matches;
            tinymce.init({
                selector: 'textarea#contentBoxID',
                license_key: 'gpl', // Indicate agreement to the GPL license
                content_css: [
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' // Include Bootstrap CSS
                ],
                plugins: 'preview searchreplace autolink autosave save code fullscreen wordcount help charmap emoticons advlist directionality emoticons image importcss insertdatetime link lists media nonbreaking pagebreak preview save searchreplace table wordcount',
                menubar: 'file edit view format tools help',
                toolbar: "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl",
                autosave_ask_before_unload: true,
                autosave_interval: '30s',
                autosave_retention: '2m',
                height: 600,
                quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
                noneditable_class: 'mceNonEditable',
                toolbar_mode: 'sliding',
                contextmenu: 'link',
                promotion: false,
                skin: useDarkMode ? 'oxide-dark' : 'oxide',
                setup: function(editor) {
                    editorInstance = editor; // Store the editor instance
                }
            });
        });
        // Load designations based on selected department
        $('#departmentSelect').on('change', function() {
            var departmentId = $(this).val(); // Get the selected department ID

            if (departmentId) {
                $.ajax({
                    url: '/admin/roless/get-designations/' + departmentId,// URL to fetch designations
                    method: 'GET',
                    success: function(data) {
                        // Clear current options
                        $('#designationSelect').html('<option selected disabled>Select Designation</option>');

                        // Add new options
                        data.designations.forEach(function(designation) {
                            $('#designationSelect').append('<option value="' + designation.id + '">' + designation.name + '</option>');
                        });
                    }
                });
            }
        });

        // Load users based on selected designation
        $('#designationSelect').on('change', function() {
            var designationId = $(this).val(); // Get the selected designation ID

            if (designationId) {
                $.ajax({
                    url: '/admin/roless/get-users/' + designationId, // URL to fetch users
                    method: 'GET',
                    success: function(data) {
                        // Clear current options
                        $('#workingDays').html('');

                        // Add new options
                        data.users.forEach(function(user) {
                            $('#workingDays').append('<option value="' + user.id + '">' + user.name + '</option>');
                        });
                    }
                });
            }
        });
    </script>
@endsection
