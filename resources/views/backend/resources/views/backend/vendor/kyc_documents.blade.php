@extends('backend.layouts.app')
@section('content')
<style>
    .text-purple { color: var(--theme-primary-color) !important; }
    .bg-purple-subtle {
        background-color: rgba(93, 26, 143, 0.1) !important;
    }
    .btn-purple {
        background-color: #5d1a8f !important;
        color: white !important;
    }
    .btn-purple:hover {
        background-color: #4a1572 !important;
        color: white !important;
    }
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
        transition: all 0.3s ease;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">KYC Verification</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Status & Bank Proof -->
            <div class="col-lg-4">
                <!-- Overall Status Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        @php
                            $isFullyVerified = $vendor_docs->count() > 0 && $vendor_docs->every(fn($doc) => $doc->is_verify == 1);
                            $hasPending = $vendor_docs->some(fn($doc) => $doc->is_verify == 0);
                            $hasRejected = $vendor_docs->some(fn($doc) => $doc->is_verify == 2);
                        @endphp

                        <div class="position-relative d-inline-block mb-4">
                            <div class="avatar-xl mx-auto d-flex align-items-center justify-content-center rounded-circle 
                                {{ $isFullyVerified ? 'bg-success-subtle text-success' : ($hasRejected ? 'bg-danger-subtle text-danger' : ($hasPending ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary')) }}" 
                                style="width: 100px; height: 100px;">
                                <iconify-icon icon="{{ $isFullyVerified ? 'solar:verified-check-linear' : ($hasRejected ? 'bg-danger-subtle text-danger' : 'solar:shield-warning-linear') }}" class="fs-48"></iconify-icon>
                            </div>
                            @if($isFullyVerified)
                                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-4 border-white rounded-circle"></span>
                            @endif
                        </div>

                        <h4 class="fw-bold mb-2">
                            @if($isFullyVerified) 
                                <span class="text-success">Verified Account</span>
                            @elseif($hasRejected)
                                <span class="text-danger">Action Required</span>
                            @elseif($hasPending)
                                <span class="text-warning">Under Review</span>
                            @else
                                <span class="text-primary">Not Verified</span>
                            @endif
                        </h4>
                        <p class="text-muted mb-4 fs-13">Complete all document uploads to enable seamless business operations and payouts.</p>

                        <div class="p-3 bg-light text-start mb-0">
                            <h6 class="fw-bold fs-12 text-uppercase text-muted mb-3">Verification Benefits</h6>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                <span class="fs-13">Secure Bank Payouts</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                <span class="fs-13">Increased Trust Score</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                <span class="fs-13">Premium Seller Badge</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Verification Card -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:card-2-linear" class="fs-20 text-purple"></iconify-icon>
                            <h5 class="mb-0 fw-bold">Bank Proof</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 border border-dashed bg-light-subtle mb-4 text-center">
                            @if(!empty($vendor->cancelled_cheque) && !Str::contains($vendor->cancelled_cheque, 'no-image.jpg'))
                                <img src="{{ $vendor->cancelled_cheque }}" class="img-fluid mb-2 shadow-sm" style="max-height: 120px; width: 100%; object-fit: scale-down;">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ $vendor->cancelled_cheque }}" target="_blank" class="btn btn-sm btn-soft-purple px-3">
                                        <iconify-icon icon="solar:eye-linear" class="align-middle me-1"></iconify-icon> View
                                    </a>
                                </div>
                            @else
                                <div class="py-3">
                                    <iconify-icon icon="solar:upload-minimalistic-linear" class="fs-40 text-muted opacity-50 mb-2"></iconify-icon>
                                    <p class="text-muted small mb-0">No cheque uploaded</p>
                                </div>
                            @endif
                        </div>

                        @if($vendor->bank_verify != 1)
                        <form action="{{ route('update.bank.proof') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Upload Cancelled Cheque</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="cancelled_cheque" class="form-control border-0 bg-light" id="chequeFile">
                                </div>
                                <small class="text-muted mt-1 d-block fs-11">JPG, PNG or PDF (Max 2MB)</small>
                            </div>
                            <button type="submit" class="btn btn-purple btn-sm w-100 py-2 shadow-sm">
                                <iconify-icon icon="solar:upload-linear" class="align-middle me-1"></iconify-icon> Update Bank Proof
                            </button>
                        </form>
                        @else
                        <div class="alert alert-success-subtle border-0 p-3 mb-0">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:verified-check-bold" class="fs-20 text-success"></iconify-icon>
                                <span class="fs-13 fw-bold text-success">Bank Details Verified</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Document List -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">Identity Documents</h5>
                            <p class="text-muted mb-0 fs-12">Total {{ $vendor_docs->count() }} documents uploaded</p>
                        </div>
                        @if(!$isFullyVerified)
                        <button type="button" class="btn btn-purple shadow-sm px-4 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                            <iconify-icon icon="solar:add-circle-linear" class="fs-18"></iconify-icon> 
                            <span>Add New Document</span>
                        </button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light-subtle text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4 border-0 py-3">Document Details</th>
                                        <th class="border-0 py-3">Status</th>
                                        <th class="border-0 py-3">Upload Date</th>
                                        <th class="border-0 text-end pe-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendor_docs as $doc)
                                        <tr class="transition-all" id="doc-row-{{ $doc->id }}" data-doc-id="{{ $doc->id }}" data-doc-type-id="{{ $doc->document_id }}" data-doc-type-name="{{ $doc->documentType->name ?? 'Identity Document' }}">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-sm bg-purple-subtle text-purple d-flex align-items-center justify-content-center">
                                                        <iconify-icon icon="solar:document-text-linear" class="fs-20"></iconify-icon>
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold d-block text-dark">{{ $doc->documentType->name ?? 'Identity Document' }}</span>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-muted small">ID: #{{ $doc->id }}</span>
                                                            @if($doc->document_number)
                                                                <span class="text-purple small fw-semibold">Number: {{ $doc->document_number }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($doc->is_verify == 1)
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                                        <iconify-icon icon="solar:check-circle-linear" class="align-middle me-1"></iconify-icon> Verified
                                                    </span>
                                                @elseif($doc->is_verify == 2)
                                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">
                                                        <iconify-icon icon="solar:close-circle-linear" class="align-middle me-1"></iconify-icon> Rejected
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1">
                                                        <iconify-icon icon="solar:clock-circle-linear" class="align-middle me-1"></iconify-icon> Pending
                                                    </span>
                                                @endif
                                                
                                                @if($doc->is_verify == 2 && $doc->rejection_reason)
                                                    <div class="mt-1">
                                                        <small class="text-danger d-block" style="max-width: 150px;">
                                                            <strong>Reason:</strong> {{ Str::limit($doc->rejection_reason, 40) }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ optional($doc->created_at)->format('d M, Y') ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ $doc->document }}" target="_blank" class="btn btn-sm btn-light text-purple shadow-sm" title="View Document">
                                                        <iconify-icon icon="solar:eye-linear" class="fs-16"></iconify-icon>
                                                    </a>
                                                    @if($doc->is_verify != 1)
                                                        <button type="button" class="btn btn-sm btn-light text-danger shadow-sm btn-delete-doc" data-id="{{ $doc->id }}" data-type-id="{{ $doc->document_id }}" data-type-name="{{ $doc->documentType->name ?? 'Identity Document' }}" title="Delete Document">
                                                            <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-16"></iconify-icon>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="py-4">
                                                    <div class="avatar-lg bg-light text-muted mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle">
                                                        <iconify-icon icon="solar:document-add-linear" class="fs-48 opacity-50"></iconify-icon>
                                                    </div>
                                                    <h6 class="fw-bold">No Documents Uploaded</h6>
                                                    <p class="text-muted small">You haven't uploaded any KYC documents yet.</p>
                                                    <button type="button" class="btn btn-purple btn-sm px-4 rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                                                        Start Uploading
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Guidelines Section -->
                <div class="card border-0 shadow-sm mt-4 bg-purple-subtle border-start border-purple border-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-xs bg-purple text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:info-circle-linear" class="fs-18"></iconify-icon>
                            </div>
                            <h6 class="mb-0 fw-bold text-purple">Important Guidelines</h6>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <iconify-icon icon="solar:check-read-linear" class="text-purple mt-1"></iconify-icon>
                                    <div>
                                        <p class="mb-0 fs-13 fw-bold">Document Clarity</p>
                                        <small class="text-muted">Ensure documents are not blurry. All text, numbers, and photos must be clearly legible.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <iconify-icon icon="solar:check-read-linear" class="text-purple mt-1"></iconify-icon>
                                    <div>
                                        <p class="mb-0 fs-13 fw-bold">Allowed Formats</p>
                                        <small class="text-muted">Only PDF, JPEG, PNG, or DOC files are accepted. Maximum file size is 5MB.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden shadow-lg">
            <div class="modal-header border-0 bg-light p-4">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:upload-square-linear" class="fs-24 text-purple"></iconify-icon>
                    <h5 class="modal-title fw-bold">Upload Document</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadDocForm" action="{{ route('vendor.profile.document.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Document Type</label>
                        @php
                            $uploadedTypeIds = $vendor_docs->pluck('document_id')->filter()->unique()->toArray();
                        @endphp
                        <select name="document_id" id="documentTypeSelect" class="form-select border-0 bg-light p-3 fs-14" required>
                            <option value="">Select the type of document</option>
                            @foreach($kyc_types as $type)
                                @if(!in_array($type->id, $uploadedTypeIds))
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="mt-2 p-2 bg-info-subtle rounded-2">
                            <small class="text-info d-flex align-items-center gap-1 fs-11">
                                <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
                                Select the correct document type to avoid rejection.
                            </small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Document Number</label>
                        <input type="text" name="document_number" id="documentNumber" class="form-control border-0 bg-light p-3 fs-14" placeholder="Enter ID / Document Number" required>
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase">Select File</label>
                        <div class="upload-container p-4 border border-2 border-dashed rounded-4 text-center bg-light-subtle transition-all hover-shadow" style="cursor: pointer;" onclick="document.getElementById('docInput').click()">
                            <iconify-icon icon="solar:cloud-upload-linear" class="fs-48 text-purple mb-2"></iconify-icon>
                            <h6 class="fw-bold mb-1">Click to Upload</h6>
                            <p class="mb-0 small text-muted">or drag and drop your file here</p>
                            <input type="file" name="document" id="docInput" class="d-none" required>
                            <div id="fileNameDisplay" class="mt-2 text-purple fw-bold fs-12 d-none"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 px-1">
                            <small class="text-muted fs-11">Max size: 5MB</small>
                            <small class="text-muted fs-11">PDF, JPG, PNG, DOC</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4 py-2 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="startUploadBtn" class="btn btn-purple rounded-3 px-4 py-2 fw-bold shadow-sm">
                        <iconify-icon icon="solar:upload-linear" class="align-middle me-1"></iconify-icon> Start Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        if (input.files && input.files[0]) {
            display.textContent = 'Selected: ' + input.files[0].name;
            display.classList.remove('d-none');
        } else {
            display.classList.add('d-none');
        }
    }
</script>

@push('scripts')
<script>
    $(function(){
        const form = $('#uploadDocForm');
        const fileInput = $('#docInput');
        const selectType = $('#documentTypeSelect');
        const submitBtn = $('#startUploadBtn');
        const fileNameDisplay = $('#fileNameDisplay');

        // Single-click flow: choose type, click upload box -> choose file -> auto submit
        $('.upload-container').on('click', function(){
            fileInput.trigger('click');
        });

        fileInput.on('change', function(){
            updateFileName(this);
        });

        form.on('submit', function(e){
            e.preventDefault();
            if (!selectType.val()) {
                toastr.warning('Please select a document type');
                return;
            }
            if (!$('#documentNumber').val()) {
                toastr.warning('Please enter the document number');
                return;
            }
            if (!fileInput.val()) {
                toastr.warning('Please select a file');
                return;
            }
            const fd = new FormData(this);
            submitBtn.prop('disabled', true).addClass('disabled').text('Uploading...');
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res){
                    if (res && res.status) {
                        toastr.success(res.message || 'Document uploaded successfully');
                        // Remove uploaded type from dropdown
                        if (Array.isArray(res.uploaded_type_ids)) {
                            selectType.find('option').each(function(){
                                const val = $(this).attr('value');
                                if (val && res.uploaded_type_ids.includes(parseInt(val))) {
                                    $(this).remove();
                                }
                            });
                        }
                        // Reset inputs
                        fileInput.val('');
                        fileNameDisplay.addClass('d-none').text('');
                        selectType.val('');
                        // Optionally reload to reflect table updates
                        location.reload();
                    } else {
                        toastr.error((res && res.message) ? res.message : 'Failed to upload document');
                    }
                },
                error: function(xhr){
                    let msg = 'Upload failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                },
                complete: function(){
                    submitBtn.prop('disabled', false).removeClass('disabled').html('<iconify-icon icon=\"solar:upload-linear\" class=\"align-middle me-1\"></iconify-icon> Start Upload');
                }
            });
        });

        // Delete document (AJAX)
        $(document).on('click', '.btn-delete-doc', function(){
            const id = $(this).data('id');
            const typeId = $(this).data('type-id');
            const typeName = $(this).data('type-name') || 'Document';
            if (!id) return;
            Swal.fire({
                title: 'Delete Document?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ route('vendor.documents.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        'delete_documents[]': id
                    },
                    success: function(res){
                        if (res && res.status) {
                            toastr.success(res.message || 'Document deleted successfully');
                            // Remove row from table
                            $('#doc-row-' + id).remove();
                            // Re-add option back to dropdown if not present
                            if (typeId) {
                                const exists = selectType.find('option[value="'+typeId+'"]').length > 0;
                                if (!exists) {
                                    selectType.append('<option value="'+typeId+'">'+typeName+'</option>');
                                }
                            }
                        } else {
                            toastr.error((res && res.message) ? res.message : 'Failed to delete document');
                        }
                    },
                    error: function(){
                        toastr.error('Something went wrong');
                    }
                });
            });
        });
    });
</script>
@endpush

@endsection

