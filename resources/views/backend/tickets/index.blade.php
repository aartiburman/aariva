@extends('backend.layouts.app')
@section('content')


<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 fs-18">Support Center</h4>
                    @if(Auth::user()->role == '2')
                    <div class="page-title-right">
                        <button class="btn btn-ticket-primary d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#createTicketCollapse">
                            <iconify-icon icon="solar:pen-new-square-linear" class="me-1"></iconify-icon> New Ticket
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if(Auth::user()->role == '2')
        @php
             $type = request('type', 'my');
        @endphp
        {{-- Vendor View --}}
        <div class="row">
            {{-- Create Ticket Form --}}
            @if($type == 'my')
            <div class="col-lg-4">
                <div class="collapse d-lg-block" id="createTicketCollapse">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 fw-bold">Create New Ticket</h5>
                            <form action="{{ route('tickets.store') }}" id="createTicketForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-13">Subject</label>
                                    <input type="text" name="subject" class="form-control" placeholder="e.g. Order #1234 issue" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-13">Message</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="Describe your issue in detail..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-13">Attachment (Optional)</label>
                                    <div class="border-dashed rounded-3 p-3 text-center cursor-pointer position-relative" style="border: 2px dashed #e9ebec; ">
                                        <input type="file" name="attachment" id="ticketAttachment" class="position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer">
                                        <iconify-icon icon="solar:upload-linear" class="fs-24 text-muted mb-1"></iconify-icon>
                                        <p class="mb-0 text-muted fs-12" id="attachmentLabel">Click to upload image or PDF</p>
                                    </div>
                                </div>
                                <div id="selectedFileName" class="text-primary fs-13 mb-1 fw-bold" style="display: none;"></div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold fs-13 d-block">Priority</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="priority" id="priorityLow" value="Low">
                                            <label class="form-check-label fs-13" for="priorityLow">Low</label>
                                        </div>
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="priority" id="priorityMedium" value="Medium" checked>
                                            <label class="form-check-label fs-13" for="priorityMedium">Medium</label>
                                        </div>
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="priority" id="priorityHigh" value="High">
                                            <label class="form-check-label fs-13" for="priorityHigh">High</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" id="submitTicketBtn" class="btn btn-primary w-100 py-2 fw-bold">
                                    Submit Ticket <iconify-icon icon="solar:send-linear" class="ms-1"></iconify-icon>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Support Hours</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-xs bg-soft-info text-info rounded-circle d-flex align-items-center justify-content-center me-2">
                                <iconify-icon icon="solar:clock-circle-linear"></iconify-icon>
                            </div>
                            <span class="fs-13 text-muted">Mon - Fri: 9:00 AM - 6:00 PM</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs bg-soft-ticket-primary text-ticket-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                <iconify-icon icon="solar:phone-linear"></iconify-icon>
                            </div>
                            <span class="fs-13 text-muted">+971 4 123 456</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tickets List --}}
            <div class="{{ $type == 'my' ? 'col-lg-8' : 'col-12' }}">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                        <h5 class="card-title mb-0 fw-bold">{{ $type == 'my' ? 'Your Tickets' : 'Customer Tickets' }}</h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-soft-ticket-primary text-ticket-primary px-3 py-2 rounded-pill fs-12">{{ $open_count }} Open</span>
                            <span class="badge bg-soft-secondary text-muted px-3 py-2 rounded-pill fs-12">{{ $closed_count }} Closed</span>
                        </div>
                    </div>
                    <div class="card-body px-4 pt-2">
                        @forelse($tickets as $ticket)
                        <div class="card border shadow-none mb-3 hover-shadow-sm transition-all rounded-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="fw-bold mb-0">
                                            <a href="{{ route('tickets.show', $ticket->id) }}" class="text-dark hover-ticket-primary">#{{ $ticket->id }} - {{ $ticket->subject }}</a>
                                        </h6>
                                        <span class="badge bg-{{ $ticket->priority == 'High' ? 'danger' : ($ticket->priority == 'Medium' ? 'info' : 'secondary') }} fs-10 px-2">
                                            {{ $ticket->priority }}
                                        </span>
                                    </div>
                                    <span class="badge rounded-pill px-3 py-1 fs-11 
                                        {{ $ticket->status == 'Open' ? 'bg-soft-ticket-primary text-ticket-primary' : 
                                           ($ticket->status == 'Closed' ? 'bg-soft-secondary text-muted' : 'bg-soft-warning text-warning') }}">
                                        {{ $ticket->status }}
                                    </span>
                                </div>
                                <p class="text-muted fs-13 mb-3 text-truncate-2">
                                    {{ Str::limit($ticket->messages->first()->message ?? 'No description available.', 150) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <iconify-icon icon="solar:user-linear" class="text-muted"></iconify-icon>
                                        </div>
                                        @if($type == 'my')
                                            <span class="text-muted fs-12">Sent to: Admin</span>
                                        @else
                                            <span class="text-muted fs-12">From: {{ $ticket->user->name ?? 'Customer' }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-muted fs-12">
                                            <iconify-icon icon="solar:calendar-linear" class="me-1"></iconify-icon>
                                            {{ $ticket->status == 'Closed' ? 'Closed on ' . $ticket->updated_at->format('M d, Y') : 'Updated ' . $ticket->updated_at->diffForHumans() }}
                                        </span>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-soft-ticket-primary rounded-pill px-3">
                                            <iconify-icon icon="solar:eye-linear" class="me-1"></iconify-icon> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:ticket-linear" class="fs-48 text-muted"></iconify-icon>
                            </div>
                            <h6 class="fw-bold">No tickets found</h6>
                            <p class="text-muted fs-13">{{ $type == 'my' ? "You haven't created any support tickets yet." : "No customer tickets found." }}</p>
                        </div>
                        @endforelse

                        <div class="mt-4">
                            {{ $tickets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        {{-- Admin View --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('tickets.index') }}" method="POST" class="row g-3 mb-4 align-items-end">
                            @csrf
                            <div class="col-md-3">
                                <label class="form-label fw-bold fs-13">Filter by Vendor</label>
                                <select name="vendor_id" class="form-select border-light bg-light shadow-none" onchange="this.form.submit()">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold fs-13">Filter by Status</label>
                                <select name="status" class="form-select border-light bg-light shadow-none" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('tickets.index') }}" class="btn btn-soft-secondary w-100">
                                    <iconify-icon icon="solar:restart-linear" class="me-1"></iconify-icon> Reset
                                </a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0 table-hover align-middle">
                                <thead class="bg-light-subtle border-bottom">
                                    <tr>
                                        <th class="ps-4">Ticket ID</th>
                                        <th>Subject</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Last Activity</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tickets as $ticket)
                                        <tr>
                                            <td class="ps-4"><span class="fw-bold text-dark">#{{ $ticket->ticket_id ?? $ticket->id }}</span></td>
                                            <td>
                                                <div class="fw-medium text-dark">{{ Str::limit($ticket->subject, 40) }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs bg-soft-ticket-primary text-ticket-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        {{ strtoupper(substr($ticket->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span>{{ $ticket->user->name }}</span>
                                                        <span class="badge bg-light text-muted border ms-1" style="font-size: 10px;">{{ $ticket->user->role == 2 ? 'Vendor' : ($ticket->user->role == 3 ? 'Customer' : 'Admin') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $ticket->receiver->name ?? 'Admin Support' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $ticket->priority == 'High' ? 'danger' : ($ticket->priority == 'Medium' ? 'info' : 'secondary') }} px-2">
                                                    {{ $ticket->priority }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-3 py-1 
                                                    {{ $ticket->status == 'Open' ? 'bg-soft-ticket-primary text-ticket-primary' : 
                                                       ($ticket->status == 'Closed' ? 'bg-soft-secondary text-muted' : 'bg-soft-warning text-warning') }}">
                                                    {{ $ticket->status }}
                                                </span>
                                            </td>
                                            <td class="text-muted fs-13">{{ $ticket->updated_at->diffForHumans() }}</td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-soft-ticket-primary" title="View Details">
                                                        <iconify-icon icon="solar:eye-linear" class="me-1"></iconify-icon> View
                                                    </a>
                                                    @if($ticket->status !== 'Closed')
                                                    <form action="{{ route('tickets.close', $ticket->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="button" class="btn btn-sm btn-soft-danger close-ticket-btn" title="Close Ticket">
                                                            <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="avatar-xl bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="solar:ticket-linear" class="fs-48 text-muted"></iconify-icon>
                                                </div>
                                                <h6 class="fw-bold">No tickets found</h6>
                                                <p class="text-muted fs-13">All support requests will appear here.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 px-4">
                            {{ $tickets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@push('scripts')

<script>
$(document).ready(function() {
    $(document).on('click', '.close-ticket-btn', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to close this ticket?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, close it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

<script>
    $(document).ready(function() {
        // File upload UI with validation
        $('#ticketAttachment').on('change', function() {
            var fileInput = this;
            var file = fileInput.files[0];
            var previewContainer = $('#attachmentPreview');
            previewContainer.empty(); // Clear previous preview

            if (file) {
                // Validate file type (only image and PDF)
                var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Only JPG, PNG, and PDF files are allowed.',
                        confirmButtonText: 'OK'
                    });
                    $(fileInput).val(''); // Clear the input
                    $('#attachmentLabel').text('Click or drag file to upload');
                    $('#attachmentLabel').addClass('text-dark').removeClass('text-success');
                    return;
                }

                // Validate file size (2MB = 2 * 1024 * 1024 bytes)
                var maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Maximum file size is 2MB. Please choose a smaller file.',
                        confirmButtonText: 'OK'
                    });
                    $(fileInput).val(''); // Clear the input
                    $('#attachmentLabel').text('Click or drag file to upload');
                    $('#attachmentLabel').addClass('text-dark').removeClass('text-success');
                    return;
                }

                var fileName = file.name;
                $('#attachmentLabel').html('<iconify-icon icon="solar:file-check-linear" class="me-1"></iconify-icon> Selected File: ' + fileName);
                $('#attachmentLabel').addClass('text-primary').removeClass('text-dark text-success');
                
                // Show filename above Priority
                $('#selectedFileName').html('<iconify-icon icon="solar:link-linear" class="me-1"></iconify-icon> File: ' + fileName).show();

                var reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        // Image preview
                        var imgContainer = $('<div class="position-relative d-inline-block mb-2"></div>');
                        var img = $('<img>').addClass('img-fluid rounded shadow-sm').attr('src', e.target.result).css({
                            'max-height': '200px',
                            'max-width': '100%',
                            'object-fit': 'contain'
                        });
                        var removeBtn = $('<button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 translate-middle" style="padding: 4px 8px;"><iconify-icon icon="solar:x-circle-linear"></iconify-icon></button>');
                        
                        removeBtn.on('click', function() {
                            $(fileInput).val('');
                            previewContainer.empty();
                            $('#attachmentLabel').text('Click or drag file to upload');
                            $('#attachmentLabel').addClass('text-dark').removeClass('text-primary text-success');
                            $('#selectedFileName').hide().text(''); // Hide filename above Priority
                        });

                        var imgInfo = $('<div class="mt-2 text-center"></div>').append(
                            $('<span class="badge bg-soft-success text-success me-1"></span>').text('Image'),
                            $('<span class="fs-12 text-muted fw-medium d-block mt-1"></span>').text(fileName),
                            $('<span class="fs-11 text-muted"></span>').text((file.size / 1024).toFixed(1) + ' KB')
                        );

                        imgContainer.append(img).append(removeBtn);
                        previewContainer.append(imgContainer).append(imgInfo);
                    } else if (file.type === 'application/pdf') {
                        // PDF preview with download link
                        var pdfContainer = $('<div class="position-relative d-inline-block mb-2"></div>');
                        var embed = $('<embed>').attr('src', e.target.result).attr('type', 'application/pdf').css({
                            'width': '300px',
                            'height': '200px',
                            'border-radius': '8px',
                            'border': '1px solid #e9ebec'
                        });
                        var removeBtn = $('<button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 translate-middle" style="padding: 4px 8px;"><iconify-icon icon="solar:x-circle-linear"></iconify-icon></button>');
                        
                        removeBtn.on('click', function() {
                            $(fileInput).val('');
                            previewContainer.empty();
                            $('#attachmentLabel').text('Click or drag file to upload');
                            $('#attachmentLabel').addClass('text-dark').removeClass('text-primary text-success');
                            $('#selectedFileName').hide().text(''); // Hide filename above Priority
                        });

                        var pdfInfo = $('<div class="mt-2 text-center"></div>').append(
                            $('<span class="badge bg-soft-primary text-primary me-1"></span>').text('PDF'),
                            $('<span class="fs-12 text-muted fw-medium d-block mt-1"></span>').text(fileName),
                            $('<span class="fs-11 text-muted"></span>').text((file.size / 1024).toFixed(1) + ' KB')
                        );

                        pdfContainer.append(embed).append(removeBtn);
                        previewContainer.append(pdfContainer).append(pdfInfo);
                    }
                };
                reader.readAsDataURL(file);
            } else {
                $('#attachmentLabel').text('Click or drag file to upload');
                $('#attachmentLabel').addClass('text-dark').removeClass('text-primary text-success');
                $('#selectedFileName').hide().text(''); // Hide filename above Priority
            }
        });

        // AJAX Form Submission
        $('#submitTicketBtn').on('click', function(e) {
            e.preventDefault();
            var form = $('#createTicketForm');
            var btn = $(this);
            var btnText = btn.find('.btn-text');
            var originalText = btnText.text();

            // Custom Validation: Message or Attachment required
            var message = (form.find('textarea[name="message"]').val() || '').trim();
            var attachmentInput = form.find('input[name="attachment"]')[0];
            var attachment = attachmentInput ? attachmentInput.files : [];
            
            if (!message && attachment.length === 0) {
                 Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please provide a message or an attachment!',
                });
                return;
            }

            // Check validity (for subject and receiver_id)
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }
            
            // Disable button & show spinner
            btn.prop('disabled', true);
            btnText.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Sending...');

            var formData = new FormData(form[0]);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = "{{ route('tickets.index') }}";
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Something went wrong. Please try again.'
                        });
                        btn.prop('disabled', false);
                        btnText.text(originalText);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = 'Something went wrong.';
                    if(errors) {
                        errorMessage = Object.values(errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                    btn.prop('disabled', false);
                    btnText.text(originalText);
                }
            });
        });
    });
</script>
@endpush
@endsection