<div class="col-12 mt-3" id="org-course-documents-section">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div>
                <h5 class="fw-bold mb-1 text-primary">
                    <i class="fas fa-file-pdf text-danger me-2"></i>Course Documents (PDF Only)
                </h5>
                <p class="text-muted small mb-0">Upload documents with title and PDF file (e.g. Doc 1 + file, Doc 2 + file). Only PDF files are accepted.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm px-3" id="btn-add-org-course-document">
                <i class="fas fa-plus-circle me-1"></i> Add More Document
            </button>
        </div>
        <div class="card-body p-4 pt-1">
            <input type="hidden" name="has_documents_form" value="1">

            <div id="org-course-documents-repeater-container">
                @php
                    $existingDocuments = isset($organisationCourse) && $organisationCourse->documents ? $organisationCourse->documents : collect();
                    $oldDocs = old('documents', null);
                @endphp

                @if(!empty($oldDocs) && is_array($oldDocs))
                    {{-- Render from old() submission in case of validation error --}}
                    @foreach($oldDocs as $idx => $oldDoc)
                        <div class="border rounded-3 p-3 mb-3 bg-white doc-repeater-row position-relative" data-index="{{ $idx }}">
                            @if(!empty($oldDoc['id']))
                                <input type="hidden" name="documents[{{ $idx }}][id]" value="{{ $oldDoc['id'] }}">
                            @endif
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary doc-row-label">Document #{{ $loop->iteration }}</span>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-doc" title="Remove Document">
                                    <i class="fas fa-trash-alt me-1"></i> Remove
                                </button>
                            </div>
                            <div class="row g-3 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small">Document Title <span class="text-danger">*</span></label>
                                    <input type="text" name="documents[{{ $idx }}][title]" class="form-control no-autosave" placeholder="e.g. Doc 1, Syllabus & Curriculum" value="{{ $oldDoc['title'] ?? '' }}">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold small">PDF File (.pdf only, max 20MB) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="file" name="documents[{{ $idx }}][file]" accept=".pdf,application/pdf" class="form-control no-autosave org-course-doc-file-input">
                                    </div>
                                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Only PDF format is accepted.</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @elseif($existingDocuments->count() > 0)
                    {{-- Render existing documents from database --}}
                    @foreach($existingDocuments as $idx => $doc)
                        <div class="border rounded-3 p-3 mb-3 bg-white doc-repeater-row position-relative" data-index="{{ $idx }}" data-doc-id="{{ $doc->id }}">
                            <input type="hidden" name="documents[{{ $idx }}][id]" value="{{ $doc->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary doc-row-label">Document #{{ $loop->iteration }}</span>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-doc" data-doc-id="{{ $doc->id }}" data-delete-url="{{ route('admin.organisation-courses.documents.destroy', [$organisationCourse->id, $doc->id]) }}" title="Delete Document">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </div>
                            <div class="row g-3 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small">Document Title <span class="text-danger">*</span></label>
                                    <input type="text" name="documents[{{ $idx }}][title]" class="form-control no-autosave" placeholder="e.g. Doc 1, Syllabus & Curriculum" value="{{ $doc->title }}">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold small">PDF File (.pdf only)</label>
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                        <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center">
                                            <i class="fas fa-file-pdf fs-5 me-2"></i>
                                            <span class="text-truncate" style="max-width: 250px;">{{ $doc->file_name ?? basename($doc->file_path) }}</span>
                                            <i class="fas fa-external-link-alt ms-2"></i>
                                        </a>
                                        @if($doc->file_size)
                                            <span class="badge bg-light text-dark border">{{ number_format($doc->file_size / 1024, 1) }} KB</span>
                                        @endif
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted small"><i class="fas fa-sync-alt me-1"></i> Replace:</span>
                                        <input type="file" name="documents[{{ $idx }}][file]" accept=".pdf,application/pdf" class="form-control no-autosave org-course-doc-file-input">
                                    </div>
                                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Leave empty to keep current file, or choose a new PDF to replace.</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Default empty initial row --}}
                    <div class="border rounded-3 p-3 mb-3 bg-white doc-repeater-row position-relative" data-index="0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary doc-row-label">Document #1</span>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-doc" title="Remove Document">
                                <i class="fas fa-trash-alt me-1"></i> Remove
                            </button>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold small">Document Title</label>
                                <input type="text" name="documents[0][title]" class="form-control no-autosave" placeholder="e.g. Doc 1, Syllabus & Curriculum">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold small">PDF File (.pdf only, max 20MB)</label>
                                <div class="input-group">
                                    <input type="file" name="documents[0][file]" accept=".pdf,application/pdf" class="form-control no-autosave org-course-doc-file-input">
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Only PDF format is accepted.</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="text-center py-2" id="org-course-doc-empty-message" style="display: none;">
                <p class="text-muted mb-2"><i class="fas fa-folder-open me-1"></i>No documents added.</p>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-org-course-doc-empty">
                    <i class="fas fa-plus me-1"></i> Add Document
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const docContainer = document.getElementById('org-course-documents-repeater-container');
    const btnAddDoc = document.getElementById('btn-add-org-course-document');
    const btnAddDocEmpty = document.getElementById('btn-add-org-course-doc-empty');
    const emptyMsg = document.getElementById('org-course-doc-empty-message');

    function updateDocLabels() {
        if (!docContainer) return;
        const rows = docContainer.querySelectorAll('.doc-repeater-row');
        if (rows.length === 0) {
            if (emptyMsg) emptyMsg.style.display = 'block';
        } else {
            if (emptyMsg) emptyMsg.style.display = 'none';
            rows.forEach((row, i) => {
                const label = row.querySelector('.doc-row-label');
                if (label) {
                    label.textContent = `Document #${i + 1}`;
                }
            });
        }
    }

    function createDocRow() {
        if (!docContainer) return;
        const newIndex = Date.now() + Math.floor(Math.random() * 1000);
        const row = document.createElement('div');
        row.className = 'border rounded-3 p-3 mb-3 bg-white doc-repeater-row position-relative';
        row.dataset.index = newIndex;
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-secondary doc-row-label">Document</span>
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-doc" title="Remove Document">
                    <i class="fas fa-trash-alt me-1"></i> Remove
                </button>
            </div>
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Document Title <span class="text-danger">*</span></label>
                    <input type="text" name="documents[\${newIndex}][title]" class="form-control no-autosave" placeholder="e.g. Doc 1, Syllabus & Curriculum">
                </div>
                <div class="col-md-7">
                    <label class="form-label fw-semibold small">PDF File (.pdf only, max 20MB) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="file" name="documents[\${newIndex}][file]" accept=".pdf,application/pdf" class="form-control no-autosave org-course-doc-file-input">
                    </div>
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Only PDF format is accepted.</small>
                </div>
            </div>
        `;
        docContainer.appendChild(row);
        updateDocLabels();
    }

    if (btnAddDoc) {
        btnAddDoc.addEventListener('click', function (e) {
            e.preventDefault();
            createDocRow();
        });
    }

    if (btnAddDocEmpty) {
        btnAddDocEmpty.addEventListener('click', function (e) {
            e.preventDefault();
            createDocRow();
        });
    }

    if (docContainer) {
        docContainer.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.btn-remove-doc');
            if (!removeBtn) return;

            const row = removeBtn.closest('.doc-repeater-row');
            if (!row) return;

            const deleteUrl = removeBtn.dataset.deleteUrl;
            if (deleteUrl) {
                if (confirm('Are you sure you want to permanently delete this document?')) {
                    removeBtn.disabled = true;
                    removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            row.remove();
                            updateDocLabels();
                        } else {
                            alert(data.message || 'Failed to delete document.');
                            removeBtn.disabled = false;
                            removeBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        alert('An error occurred while deleting the document.');
                        removeBtn.disabled = false;
                        removeBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
                    });
                }
            } else {
                row.remove();
                updateDocLabels();
            }
        });

        docContainer.addEventListener('change', function (e) {
            if (e.target.classList.contains('org-course-doc-file-input')) {
                const input = e.target;
                const file = input.files && input.files[0];
                if (file) {
                    const isPdf = file.name.toLowerCase().endsWith('.pdf') || file.type === 'application/pdf';
                    if (!isPdf) {
                        alert('Only PDF files are allowed! Please select a valid .pdf file.');
                        input.value = '';
                        return;
                    }

                    if (file.size > 20 * 1024 * 1024) {
                        alert('File size exceeds 20MB limit. Please upload a smaller PDF file.');
                        input.value = '';
                        return;
                    }

                    const row = input.closest('.doc-repeater-row');
                    if (row) {
                        const titleInput = row.querySelector('input[name*="[title]"]');
                        if (titleInput && !titleInput.value.trim()) {
                            let baseName = file.name.replace(/\.[^/.]+$/, "");
                            titleInput.value = baseName;
                        }
                    }
                }
            }
        });
    }

    updateDocLabels();
});
</script>
@endpush
