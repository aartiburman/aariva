@extends('backend.layouts.app')

@section('content')


<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fs-18">Create New Ticket</h4>

                        <p class="text-muted mb-0 fs-13">Submit a new support request</p>
                    </div>
                    <div class="page-title-right">
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="solar:arrow-left-linear" class="me-1"></iconify-icon> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form id="createTicketForm" action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            @if(isset($vendors) && count($vendors) > 0)
                            <div class="mb-4">
                                <label class="form-label fw-bold fs-13">Select Vendor</label>
                                <select name="receiver_id" class="form-select border-light bg-light shadow-none py-2" required>
                                    <option value="">Choose a vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }} {{ $vendor->store_name ? '('.$vendor->store_name.')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="mb-4">
                                <label class="form-label fw-bold fs-13">Subject</label>
                                <input type="text" name="subject" class="form-control border-light bg-light shadow-none py-2" placeholder="e.g. Order #1234 issue" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold fs-13 d-block">Priority</label>
                                <div class="d-flex gap-4">
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

                            <div class="mb-4">
                                <label class="form-label fw-bold fs-13">Message</label>
                                <textarea name="message" class="form-control border-light bg-light shadow-none" rows="6" placeholder="Please provide detailed information about your issue..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold fs-13">Attachment (Optional)</label>
                                <div class="border-dashed rounded-3 p-4 text-center cursor-pointer position-relative" style="border: 2px dashed #e9ebec; background-color: #f8f9fa;">
                                    <input type="file" name="attachment" id="ticketAttachment" class="position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer" accept="image/*,application/pdf">
                                    <iconify-icon icon="solar:upload-linear" class="fs-32 text-muted mb-2"></iconify-icon>
                                    <p class="mb-1 text-dark fw-medium" id="attachmentLabel">Click or drag file to upload</p>
                                    <p class="mb-0 text-muted fs-12">Max size: 2MB. Allowed types: JPG, PNG, PDF</p>
                                    <div id="attachmentPreview" class="mt-3"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">

                                <button type="button" id="submitTicketBtn" class="btn btn-ticket-primary px-4 fw-bold">
                                    <span class="btn-text">Submit Ticket</span> 
                                    <iconify-icon icon="solar:send-linear" class="ms-1"></iconify-icon>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Support Information</h6>
                        <p class="text-muted fs-13 mb-4">Our support team is dedicated to providing you with the best assistance possible. Please be clear and concise when describing your issue.</p>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-xs bg-soft-info text-info rounded-circle d-flex align-items-center justify-content-center me-2">
                                <iconify-icon icon="solar:clock-circle-linear"></iconify-icon>
                            </div>
                            <span class="fs-13 text-muted">Response time: within 24 hours</span>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-xs bg-soft-ticket-primary text-ticket-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                <iconify-icon icon="solar:shield-check-linear"></iconify-icon>
                            </div>
                            <span class="fs-13 text-muted">Secure & confidential support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection