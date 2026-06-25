@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fs-18 fw-bold">Ticket: #{{ $ticket->ticket_id }}</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}" class="text-muted">Support Center</a></li>
                                <li class="breadcrumb-item active">Ticket Details</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        @if(Auth::user()->role == '1' && $ticket->status !== 'Closed')
                        <button type="button" id="closeTicketBtn" class="btn btn-danger rounded-3 px-3 shadow-sm">
                            <iconify-icon icon="solar:close-circle-linear" class="me-1 fs-18"></iconify-icon> Close Ticket
                        </button>
                        <form id="closeTicketForm" action="{{ route('tickets.close', $ticket->id) }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        @endif

                        @if(Auth::user()->role == '2' && $ticket->status !== 'Closed' && $ticket->status !== 'Escalated')
                        <button type="button" id="escalateTicketBtn" class="btn btn-warning rounded-3 px-3 shadow-sm text-white">
                            <iconify-icon icon="solar:transfer-horizontal-linear" class="me-1 fs-18"></iconify-icon> Escalate to Admin
                        </button>
                        <form id="escalateTicketForm" action="{{ route('tickets.escalate', $ticket->id) }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        @endif

                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
                            <iconify-icon icon="solar:arrow-left-linear" class="me-1 fs-18"></iconify-icon> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Ticket Conversation Thread --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-bottom border-light p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 fw-bold">Conversation</h5>
                            <span class="badge rounded-pill px-3 py-1 fs-12 
                                {{ $ticket->status == 'Open' ? 'bg-soft-ticket-primary text-ticket-primary' : 
                                   ($ticket->status == 'Closed' ? 'bg-soft-secondary text-muted' : 'bg-soft-warning text-warning') }}">
                                <iconify-icon icon="solar:record-circle-linear" class="me-1 fs-12"></iconify-icon> {{ $ticket->status }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Subject & Initial Message --}}
                        <div class="mb-4 pb-4 border-bottom border-light">
                            <h4 class="fw-bold mb-3 text-body">{{ $ticket->subject }}</h4>
                            <div class="d-flex gap-3">
                                <div class="avatar-sm bg-soft-ticket-primary rounded-3 flex-shrink-0 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:chat-round-dots-linear" class="fs-20 text-ticket-primary"></iconify-icon>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted fs-13 mb-0">Original Message:</p>
                                    <p class="mb-2 text-body">{{ $ticket->messages->first()->message ?? 'No content' }}</p>
                                    
                                    @php $firstMsg = $ticket->messages->first(); @endphp
                                    @if($firstMsg && $firstMsg->getRawAttachment())
                                        <div class="mt-2">
                                            @if($firstMsg->isImage())
                                                <div class="attachment-preview mb-2">
                                                    <a href="{{ $firstMsg->attachment }}" target="_blank">
                                                        <img src="{{ $firstMsg->attachment }}" class="img-fluid rounded-3 border" style="max-height: 200px; object-fit: contain;" alt="Attachment">
                                                    </a>
                                                </div>
                                            @elseif($firstMsg->isPdf())
                                                <div class="d-flex align-items-center p-2 bg-light rounded-3" style="width: fit-content;">
                                                    <iconify-icon icon="solar:file-text-linear" class="me-2 fs-20 text-muted"></iconify-icon>
                                                    <a href="{{ $firstMsg->attachment }}" target="_blank" class="fs-13 fw-medium text-dark text-truncate" style="max-width: 180px;">
                                                        {{ $firstMsg->getAttachmentName() }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Conversation --}}
                        <div id="conversation-wrapper" class="conversation-wrapper pe-2" style="max-height: 500px; overflow-y: auto;">
                            @foreach($ticket->messages as $index => $message)
                                @continue($index === 0 && $ticket->messages->count() > 1) {{-- Skip first message as it's shown above if there are replies --}}
                                @if($index === 0 && $ticket->messages->count() === 1)
                                    {{-- If only one message, it's already shown in the header-like section above --}}
                                    <div class="text-center my-4">
                                        <p class="text-muted fs-12 mb-0">No replies yet</p>
                                    </div>
                                @else
                                <div class="d-flex mb-4 {{ $message->user_id === Auth::id() ? 'justify-content-end' : 'justify-content-start' }}">
                                    @if($message->user_id !== Auth::id())
                                    <div class="avatar-sm me-3 mt-auto">
                                        <div class="avatar-title bg-soft-ticket-primary text-ticket-primary rounded-circle fw-bold">
                                            {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    @endif
                                    <div style="max-width: 80%;">
                                        <div class="p-3 rounded-4 mb-2 shadow-sm {{ $message->user_id === Auth::id() ? 'bg-ticket-me text-white bubble-me' : 'bg-light text-dark bubble-other' }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="fw-bold fs-11 {{ $message->user_id === Auth::id() ? 'text-white-50' : 'text-ticket-primary' }}">
                                                    {{ $message->user->name }}
                                                </small>
                                                <small class="{{ $message->user_id === Auth::id() ? 'text-white-50' : 'text-muted' }} fs-10 ms-3">
                                                    {{ $message->created_at->format('h:i A') }}
                                                </small>
                                            </div>
                                            <p class="mb-0 fs-14 lh-base">{{ $message->message }}</p>
                                            
                                            @if($message->getRawAttachment())
                                            <div class="mt-3 pt-2 border-top {{ $message->user_id === Auth::id() ? 'border-white-10' : 'border-dark-10' }}">
                                                @if($message->isImage())
                                                    <div class="attachment-preview mb-2">
                                                        <a href="{{ $message->attachment }}" target="_blank">
                                                            <img src="{{ $message->attachment }}" class="img-fluid rounded-3 border" style="max-height: 200px; object-fit: contain;" alt="Attachment">
                                                        </a>
                                                    </div>
                                                @endif

                                                <!-- <a href="{{ $message->attachment }}" target="_blank" 
                                                   class="d-inline-flex align-items-center fs-12 fw-medium {{ $message->user_id === Auth::id() ? 'text-white' : 'text-ticket-primary' }}">
                                                    <iconify-icon icon="{{ $message->isPdf() ? 'solar:file-text-linear' : ($message->isImage() ? 'solar:gallery-linear' : 'solar:file-download-linear') }}" class="me-1 fs-16"></iconify-icon>
                                                    {{ $message->getAttachmentName() }}
                                                </a> -->
                                            </div>
                                            @endif
                                        </div>
                                        <div class="d-flex {{ $message->user_id === Auth::id() ? 'justify-content-end' : 'justify-content-start' }} align-items-center px-1">
                                            <small class="text-muted fs-11">{{ $message->created_at->diffForHumans() }}</small>
                                            @if($message->user_id === Auth::id())
                                            <iconify-icon icon="solar:check-read-linear" class="ms-1 text-ticket-primary fs-14"></iconify-icon>
                                            @endif
                                        </div>
                                    </div>
                                    @if($message->user_id === Auth::id())
                                    <div class="avatar-sm ms-3 mt-auto">
                                        <div class="avatar-title bg-soft-ticket-primary text-ticket-primary rounded-circle fw-bold">
                                            {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Reply Section --}}
                        @if($ticket->status !== 'Closed')
                            @if($ticket->status == 'Escalated' && Auth::user()->role == '2')
                                <div class="alert bg-soft-warning text-warning border-0 rounded-4 text-center mt-4 mb-0 py-4">
                                    <iconify-icon icon="solar:lock-password-linear" class="fs-24 mb-2 d-block mx-auto text-warning"></iconify-icon> 
                                    <p class="mb-0 fw-medium">This ticket has been escalated to <strong>Admin</strong>. You can view messages but cannot reply.</p>
                                </div>
                            @else
                                <div class="reply-section mt-4 pt-4 border-top border-light">
                                    <form id="reply-form" action="{{ route('tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="no-loader">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-bold fs-13 text-body">Quick Reply</label>
                                            <textarea name="message" class="form-control border-0 shadow-none bg-light p-3 rounded-4" rows="4" placeholder="Type your message here..."></textarea>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1 me-3">
                                                <div class="position-relative">
                                                    <input type="file" name="attachment" id="ticketReplyAttachment" class="position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer" style="z-index: 2;">
                                                    <div class="btn btn-sm btn-soft-ticket-primary rounded-3 px-3 d-inline-flex align-items-center">
                                                        <iconify-icon icon="solar:upload-linear" class="me-1 fs-16"></iconify-icon> 
                                                        <span id="replyAttachmentLabel">Add Attachment</span>
                                                    </div>
                                                    <small class="text-muted ms-2 fs-11">Max 2MB (JPG, PNG, PDF)</small>
                                                </div>
                                                <div id="replyAttachmentPreview" class="mt-2"></div>
                                            </div>
                                            <button type="button" id="submitReplyBtn" class="btn btn-ticket-primary rounded-3 px-4 fw-bold shadow-sm">
                                                Send Reply <iconify-icon icon="solar:send-linear" class="ms-1 fs-16"></iconify-icon>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @else
                        <div class="alert bg-soft-secondary text-muted border-0 rounded-4 text-center mt-4 mb-0 py-4">
                            <iconify-icon icon="solar:lock-linear" class="fs-24 mb-2 d-block mx-auto text-secondary"></iconify-icon> 
                            <p class="mb-0 fw-medium">This ticket has been marked as <strong>Closed</strong>. You cannot send further replies.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar Info --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4 fw-bold">Ticket Details</h5>
                        
                        <div class="d-flex flex-column gap-4">
                            <div>
                                <label class="text-muted fs-11 text-uppercase fw-bold mb-2 d-block letter-spacing-1">Priority Level</label>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $priorityClass = [
                                            'High' => 'bg-danger',
                                            'Medium' => 'bg-warning',
                                            'Low' => 'bg-info'
                                        ][$ticket->priority] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $priorityClass }} rounded-pill px-3 py-1 fs-12 shadow-sm">{{ $ticket->priority }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="text-muted fs-11 text-uppercase fw-bold mb-2 d-block letter-spacing-1">Raised By</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-soft-ticket-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <span class="text-ticket-primary fw-bold">{{ strtoupper(substr($ticket->user->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-14 fw-bold text-body">{{ $ticket->user->name }}</h6>
                                        <small class="text-muted">{{ $ticket->user->email }}</small>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-muted fs-11 text-uppercase fw-bold mb-2 d-block letter-spacing-1">Assigned To</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-soft-secondary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <iconify-icon icon="solar:user-bold-duotone" class="text-secondary fs-18"></iconify-icon>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-14 fw-bold text-body">{{ $ticket->receiver->name ?? 'Admin Support' }}</h6>
                                        <small class="text-muted">Support Department</small>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-top border-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted fs-13">Created On:</span>
                                    <span class="text-body fs-13 fw-medium">{{ $ticket->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted fs-13">Last Update:</span>
                                    <span class="text-body fs-13 fw-medium">{{ $ticket->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-soft-ticket-primary">
                    <div class="card-body p-4 text-center">
                        <div class="bg-white rounded-circle avatar-lg d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm">
                            <iconify-icon icon="solar:question-square-linear" class="fs-32 text-dark"></iconify-icon>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Support Guidelines</h6>
                        <p class="text-muted fs-12 mb-0">Please be detailed in your replies to help us resolve your issue faster. We typically respond within 24 hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Escalate Ticket with SweetAlert
        $('#escalateTicketBtn').on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to escalate this ticket to Admin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, escalate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#escalateTicketForm').submit();
                }
            });
        });

        // Close Ticket with SweetAlert
        $('#closeTicketBtn').on('click', function() {
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
                    $('#closeTicketForm').submit();
                }
            });
        });

        // File input handler with preview
        $('#ticketReplyAttachment').on('change', function(e) {
            const fileInput = this;
            const file = e.target.files[0];
            const previewContainer = $('#replyAttachmentPreview');
            previewContainer.empty();

            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Only JPG, PNG, and PDF files are allowed.',
                    });
                    $(fileInput).val('');
                    $('#replyAttachmentLabel').text('Add Attachment');
                    return;
                }

                // Validate size
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Maximum file size is 2MB.',
                    });
                    $(fileInput).val('');
                    $('#replyAttachmentLabel').text('Add Attachment');
                    return;
                }

                const fileName = file.name;
                $('#replyAttachmentLabel').text(fileName);

                const reader = new FileReader();
                reader.onload = function(e) {
                    let previewContent;
                    if (file.type.startsWith('image/')) {
                        previewContent = $('<div>').addClass('position-relative d-inline-block').append(
                            $('<img>').attr('src', e.target.result).addClass('rounded border shadow-sm').css({'max-height': '100px', 'max-width': '100%'}),
                            $('<button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 translate-middle" style="padding: 2px 5px;"><iconify-icon icon="solar:x-circle-linear"></iconify-icon></button>').on('click', function() {
                                $(fileInput).val('');
                                previewContainer.empty();
                                $('#replyAttachmentLabel').text('Add Attachment');
                            })
                        );
                    } else if (file.type === 'application/pdf') {
                        previewContent = $('<div>').addClass('position-relative d-inline-block p-2 bg-soft-primary rounded border').append(
                            $('<iconify-icon icon="solar:file-text-linear" class="fs-24 text-primary me-2 align-middle"></iconify-icon>'),
                            $('<span class="fs-12 fw-medium text-dark align-middle"></span>').text(fileName),
                            $('<button type="button" class="btn btn-sm btn-danger rounded-circle ms-2 align-middle" style="padding: 2px 5px;"><iconify-icon icon="solar:x-circle-linear"></iconify-icon></button>').on('click', function() {
                                $(fileInput).val('');
                                previewContainer.empty();
                                $('#replyAttachmentLabel').text('Add Attachment');
                            })
                        );
                    }
                    previewContainer.append(previewContent);
                };
                reader.readAsDataURL(file);
            } else {
                $('#replyAttachmentLabel').text('Add Attachment');
            }
        });
        
        // Submit Reply Button
        $('#submitReplyBtn').on('click', function() {
            var message = $('textarea[name="message"]').val();
            var attachment = $('#ticketReplyAttachment')[0].files;

            if (!message.trim() && attachment.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a message or upload an attachment!',
                });
                return;
            }
            
            // Show loading state
            var btn = $(this);
            var originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Sending...');
            
            var form = $('#reply-form')[0];
            var formData = new FormData(form);

            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                global: false,
                success: function(response) {
                    if(response.success) {
                        // Clear input
                        $('textarea[name="message"]').val('');
                        $('#ticketReplyAttachment').val('');
                        $('#replyAttachmentLabel').text('Add Attachment');
                        $('#replyAttachmentPreview').empty();
                        
                        // Append message
                        if(response.data) {
                            appendMessage(response.data);
                            lastMessageId = response.data.id;
                            scrollToBottom();
                        }

                        // Reset button
                        btn.prop('disabled', false).html(originalText);

                        // Optional: Show toast/notification
                         Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Reply sent successfully',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    var errorMessage = 'Something went wrong!';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                }
            });
        });

        // Chat functionality
        let lastMessageId = "{{ $ticket->messages->last()->id ?? 0 }}";
        const ticketId = "{{ $ticket->id }}";
        const authId = "{{ Auth::id() }}";
        const messagesUrl = "{{ route('tickets.messages', $ticket->id) }}";
        
        // Scroll to bottom initially
        const container = document.getElementById('conversation-wrapper');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        function scrollToBottom() {
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function fetchMessages() {
            $.ajax({
                url: messagesUrl,
                method: 'GET',
                data: { last_id: lastMessageId },
                global: false, // Disable global loading events
                success: function(response) {
                    if (response.messages && response.messages.length > 0) {
                        $('#conversation-wrapper .text-center').remove(); // Remove "No replies yet"
                        
                        response.messages.forEach(function(msg) {
                            if (msg.id > lastMessageId) {
                                appendMessage(msg);
                                lastMessageId = msg.id;
                            }
                        });
                        scrollToBottom();
                    }
                }
            });
        }

        function appendMessage(msg) {
            const isMe = msg.user_id == authId;
            const alignClass = isMe ? 'justify-content-end' : 'justify-content-start';
            // Styles for bubbles
            const bubbleClass = isMe ? 'bg-ticket-me text-white bubble-me' : 'bg-light text-dark bubble-other';
            const nameColor = isMe ? 'text-white-50' : 'text-ticket-primary';
            const timeColor = isMe ? 'text-white-50' : 'text-muted';
            
            const avatarHtml = !isMe ? `
                <div class="avatar-sm me-3 mt-auto">
                    <div class="avatar-title bg-soft-ticket-primary text-ticket-primary rounded-circle fw-bold">
                        ${msg.user.name.charAt(0).toUpperCase()}
                    </div>
                </div>` : '';
            
            const myAvatarHtml = isMe ? `
                <div class="avatar-sm ms-3 mt-auto">
                    <div class="avatar-title bg-soft-ticket-primary text-ticket-primary rounded-circle fw-bold">
                        ${msg.user.name.charAt(0).toUpperCase()}
                    </div>
                </div>` : '';

            const extension = msg.attachment ? msg.attachment.split(/[#?]/)[0].split('.').pop().trim().toLowerCase() : '';
            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
            const isPdf = extension === 'pdf';
            const icon = isPdf ? 'solar:file-text-linear' : (isImage ? 'solar:gallery-linear' : 'solar:file-download-linear');

            const attachmentHtml = msg.attachment ? `
                <div class="mt-3 pt-2 border-top ${isMe ? 'border-white-10' : 'border-dark-10'}">
                    ${isImage ? `
                        <div class="attachment-preview mb-2">
                            <a href="${msg.attachment}" target="_blank">
                                <img src="${msg.attachment}" class="img-fluid rounded-3 border" style="max-height: 200px; object-fit: contain;" alt="Attachment">
                            </a>
                        </div>
                    ` : `
                        <div class="d-flex align-items-center p-2 bg-light rounded-3">
                            <iconify-icon icon="${icon}" class="me-2 fs-20 text-muted"></iconify-icon>
                            <a href="${msg.attachment}" target="_blank" class="fs-13 fw-medium text-dark text-truncate" style="max-width: 180px;">
                                ${msg.attachment.split('/').pop().split(/[#?]/)[0]}
                            </a>
                        </div>
                    `}
                </div>` : '';

            const html = `
                <div class="d-flex mb-4 ${alignClass}">
                    ${avatarHtml}
                    <div style="max-width: 80%;">
                        <div class="p-3 rounded-4 mb-2 shadow-sm ${bubbleClass}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-bold fs-11 ${nameColor}">
                                    ${msg.user.name}
                                </small>
                                <small class="${timeColor} fs-10 ms-3">
                                    ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </small>
                            </div>
                            <p class="mb-0 fs-14 lh-base">${msg.message}</p>
                            ${attachmentHtml}
                        </div>
                        <div class="d-flex ${isMe ? 'justify-content-end' : 'justify-content-start'} align-items-center px-1">
                            <small class="text-muted fs-11">Just now</small>
                            ${isMe ? '<iconify-icon icon="solar:check-read-linear" class="ms-1 text-ticket-primary fs-14"></iconify-icon>' : ''}
                        </div>
                    </div>
                    ${myAvatarHtml}
                </div>
            `;
            
            $('#conversation-wrapper').append(html);
        }

        // Poll for new messages every 10 seconds
        setInterval(fetchMessages, 10000);
    });
</script>
@endsection