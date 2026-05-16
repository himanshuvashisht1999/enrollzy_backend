@extends('admin.layouts.master')

@section('title', 'Consultant Settings Master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Consultant Types -->
        @component('admin.consultant_settings.card', [
            'title' => 'Consultant Types',
            'items' => $types,
            'storeRoute' => 'admin.consultant-settings.store-type',
            'updateRoute' => 'admin.consultant-settings.update-type',
            'destroyRoute' => 'admin.consultant-settings.destroy-type',
            'placeholder' => 'e.g. Agency, Individual'
        ]) @endcomponent

        <!-- Consultant Statuses -->
        @component('admin.consultant_settings.card', [
            'title' => 'Consultant Statuses',
            'items' => $statuses,
            'storeRoute' => 'admin.consultant-settings.store-status',
            'updateRoute' => 'admin.consultant-settings.update-status',
            'destroyRoute' => 'admin.consultant-settings.destroy-status',
            'placeholder' => 'e.g. Active, Pending'
        ]) @endcomponent

        <!-- Access Levels -->
        @component('admin.consultant_settings.card', [
            'title' => 'Access Levels',
            'items' => $access_levels,
            'storeRoute' => 'admin.consultant-settings.store-access-level',
            'updateRoute' => 'admin.consultant-settings.update-access-level',
            'destroyRoute' => 'admin.consultant-settings.destroy-access-level',
            'placeholder' => 'e.g. Premium, Standard'
        ]) @endcomponent

        <!-- Lead Visibilities -->
        @component('admin.consultant_settings.card', [
            'title' => 'Lead Visibilities',
            'items' => $lead_visibilities,
            'storeRoute' => 'admin.consultant-settings.store-visibility',
            'updateRoute' => 'admin.consultant-settings.update-visibility',
            'destroyRoute' => 'admin.consultant-settings.destroy-visibility',
            'placeholder' => 'e.g. Own Only, All organization'
        ]) @endcomponent
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editMasterModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form id="editMasterForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold">Edit <span id="modalTargetTitle"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Name</label>
                        <input type="text" name="name" id="editMasterName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
    function editMaster(id, name, url, title) {
        $('#editMasterForm').attr('action', url);
        $('#editMasterName').val(name);
        $('#modalTargetTitle').text(title);
        $('#editMasterModal').modal('show');
    }
</script>
@endpush

