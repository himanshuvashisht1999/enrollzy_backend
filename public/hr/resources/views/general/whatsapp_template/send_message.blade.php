@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
<style>
.bootstrap-tagsinput {
  width: 100%;
  min-height: 40px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}
.bootstrap-tagsinput .tag {
  margin: 2px;
  background: #007bff;
  color: #fff;
  border-radius: 4px;
  padding: 4px 8px;
}
</style>
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Whatsapp Template </h6>
            </div>
                <div class="card-body">
                    <form id="addWhatsappTemplateForm" action="{{ route('admin.whatsapp_template.postSendMessage') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <!-- <div class="col-md-12 form-group">
                                <label for="numbers">Enter Numbers (comma or line separated)</label>
                                <textarea name="numbers" class="form-control" rows="3" placeholder="e.g. 9876543210, 9123456789"></textarea>
                                <small class="text-muted">You can also select a user category below.</small>
                            </div> -->
                            <div class="col-md-3 form-group">
                                <label for="time_gap">Min Time Gap Between Messages (seconds)</label>
                                <input type="number" class="form-control" name="min_time_gap" value="10" min="1">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="time_gap">Max Time Gap Between Messages (seconds)</label>
                                <input type="number" class="form-control" name="max_time_gap" value="50" min="1">
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="batch_size">Send in Batches of &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp</label>
                                <input type="number" class="form-control" name="batch_size" value="5" min="1">
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="batch_gap">Time Gap Between Batches (seconds)</label>
                                <input type="number" class="form-control" name="batch_gap" value="35" min="1">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="batch_gap">Caption</label>
                                <input type="text" class="form-control" name="caption" value="{{$whatsapp_template->caption}}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="image">Image</label>
                                <input type="file" class="form-control" name="image" accept=".jpg, .jpeg, .png">
                                <small id="fileHelp" class="form-text text-muted">
                                    Upload an image (jpg, jpeg, png) not exceeding 2MB.
                                </small>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="start_time">Start Time</label>
                                <input type="datetime-local" class="form-control" name="start_time" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>

                            <div class="col-md-4 form-group">
                                <label for="start_pausing_time">Start Pausing Time</label>
                                <input type="datetime-local" class="form-control" name="start_pausing_time" value="">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="end_pausing_time">End Pausing Time</label>
                                <input type="datetime-local" class="form-control" name="end_pausing_time" value="">
                            </div>
                            
                            
                            
                            <div class="col-md-12 form-group">
                                <label for="name"> Message</label>
                                
                                <textarea name="message" class="form-control"  id="message-editor"  placeholder="Enter message">{{$whatsapp_template->message}}</textarea>
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="user_category"> User Category</label>
                                <select name="user_categories[]" id="user_categories" class="form-control" multiple>
                                    <option value="">Select User Category</option>

                                    @php
                                        // Get already selected values (for edit / validation error cases)
                                        $selectedUserCategories = old('user_categories', $selectedUserCategories ?? []);

                                        // Avoid function redeclaration
                                        if (!function_exists('renderUserCategoryOptions')) {
                                            function renderUserCategoryOptions($categories, $selected = [], $level = 0) {
                                                foreach ($categories as $cat) {
                                                    // Check if this category is selected
                                                    $isSelected = in_array($cat->id, $selected) ? ' selected' : '';

                                                    echo '<option value="'.$cat->id.'"'.$isSelected.'>';

                                                    // Indentation based on level
                                                    echo str_repeat("— ", $level).$cat->name;

                                                    // Append users_count if available
                                                    if (isset($cat->users_count)) {
                                                        echo ' ('.$cat->users_count.')';
                                                    }

                                                    echo '</option>';

                                                    // Recurse through children
                                                    if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                                        renderUserCategoryOptions($cat->childrenRecursive, $selected, $level + 1);
                                                    }
                                                }
                                            }
                                        }
                                    @endphp

                                    {{-- Call recursive renderer --}}
                                    @php renderUserCategoryOptions($categories, $selectedUserCategories); @endphp

                                </select>
                            </div>

                            <div class="col-md-12 form-group">
                                <label for="numbers">Enter Numbers</label>
                                <input type="text" id="numbers" name="numbers" data-role="tagsinput" class="form-control" placeholder="Type number and press Enter">
                                <small class="text-muted">Enter multiple numbers by pressing Enter or comma.</small>
                                <div id="numberCount" class="mt-2 font-weight-bold text-primary">Total Numbers: 0</div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="card-footer text-right">
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.whatsapp_template.index') }}">Cancel </a>
                    <button type="submit" form="addWhatsappTemplateForm" class="btn btn-primary btn-sm">Add</button>
                </div>
        </div>
    </div>
@endsection

@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('message-editor', {
            
        });
    </script>
    
    <script src="{{ URL::asset('admin/js/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ URL::asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>
    <script>
        $('#image').filemanager('image');
        const useDarkMode = false; // Always set to false to force light mode
        const isSmallScreen = window.matchMedia('(max-width: 1023.5px)').matches;
        tinymce.init({
            selector: 'textarea#premiumskinsandicons-snow',
            plugins: 'preview searchreplace autolink autosave save code fullscreen wordcount help charmap emoticons',
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
            content_css: useDarkMode ? 'dark' : 'default',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
        });
    </script>
    


<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
<script>
$(document).ready(function(){

    // Function to update total number count
    function updateNumberCount() {
        const total = $('#numbers').tagsinput('items').length;
        $('#numberCount').text('Total Numbers: ' + total);
    }

    // Validate and update on add
    $('#numbers').on('itemAdded', function(event) {
        let value = event.item;
        if (!/^\d{10}$/.test(value)) {
            alert('Please enter a valid 10-digit number');
            $(this).tagsinput('remove', value);
        }
        updateNumberCount();
    });

    // Update on remove
    $('#numbers').on('itemRemoved', function() {
        updateNumberCount();
    });

    // When category changes
    $('#user_categories').on('change', function() {
        let selectedCategories = $(this).val();
        if (!selectedCategories || selectedCategories.length === 0) {
            $('#numbers').tagsinput('removeAll');
            updateNumberCount();
            return;
        }

        $.ajax({
            url: "{{ route('admin.whatsapp_template.getCategoryNumbers') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                category_ids: selectedCategories
            },
            success: function(response) {
                if (response.numbers && response.numbers.length > 0) {
                    // Keep manual numbers if you want to combine them
                    let existingNumbers = $('#numbers').tagsinput('items');
                    $('#numbers').tagsinput('removeAll');

                    // Merge category + existing (optional)
                    let combinedNumbers = [...new Set([...existingNumbers, ...response.numbers])];

                    combinedNumbers.forEach(function(num) {
                        $('#numbers').tagsinput('add', num);
                    });
                } else {
                    $('#numbers').tagsinput('removeAll');
                }

                updateNumberCount();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Initialize count on page load
    updateNumberCount();
});
</script>

@endsection


