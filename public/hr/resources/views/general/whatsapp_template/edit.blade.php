@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Create Whatsapp Template </h6>
            </div>
                <div class="card-body">
                    <form id="updateWhatsappForm" action="{{ route('admin.whatsapp_template.update', encrypt($data->id)) }}"
                        method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name"> Name</label>
                                <input type="text" class="form-control" name="name" placeholder="name"
                                    value="{{ $data->name }}">
                            </div>
                           
                            <div class="col-md-6 form-group">
                                <label for="status"> Status</label>
                                <select name="status" id="" class="form-control">
                                    <option {{ $data->status == 'active' ? 'selected' : '' }} value="active">Active</option>
                                    <option {{ $data->status == 'inactive' ? 'selected' : '' }} value="inactive">InActive
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="caption">Caption</label>
                                <input type="text" class="form-control" name="caption" value="{{$data->caption}}">
                            </div>
                            
                          
                            <div class="col-md-12 form-group">
                                <label for="name"> Message</label>
                                
                                <textarea name="message" class="form-control"  id="message-editor"  placeholder="Enter message">{{ $data->message }}</textarea>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="card-footer text-right">
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.whatsapp_template.index') }}">Cancel </a>
                    <button type="submit" form="updateWhatsappForm" class="btn btn-primary btn-sm">Update</button>
                </div>
        </div>
    </div>
@endsection
@section('push_script')
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
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('message-editor', {
            
        });
    </script>
@endsection
