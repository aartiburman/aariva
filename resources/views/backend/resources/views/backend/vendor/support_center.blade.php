@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Support Center</h4>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Support Info & Contact -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="avatar-xl bg-purple-subtle text-purple rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="background-color: #f3e8ff !important; color: #5d1a8f !important; width: 80px; height: 80px;">
                            <iconify-icon icon="solar:chat-round-dots-linear" class="fs-40"></iconify-icon>
                        </div>
                        <h4 class="fw-bold mb-2">How can we help?</h4>
                        <p class="text-muted mb-4">Our support team is available 24/7 to assist you with any issues or questions regarding your store.</p>
                        
                        <div class="d-grid gap-2">
                            <a href="mailto:support@aariva.com" class="btn btn-purple py-2 rounded-3" style="background-color: #5d1a8f; color: white;">
                                <iconify-icon icon="solar:letter-linear" class="align-middle me-2"></iconify-icon> Email Support
                            </a>
                            <a href="https://wa.me/yourwhatsappnumber" class="btn btn-success py-2 rounded-3">
                                <iconify-icon icon="solar:phone-calling-linear" class="align-middle me-2"></iconify-icon> WhatsApp Chat
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">Quick Links</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="list-group list-group-flush gap-2 border-0">
                            <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 bg-light-subtle d-flex align-items-center gap-3 p-3">
                                <div class="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:book-2-linear"></iconify-icon>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-14">Vendor Guide</h6>
                                    <small class="text-muted">Learn how to manage store</small>
                                </div>
                                <iconify-icon icon="solar:alt-arrow-right-linear" class="ms-auto text-muted"></iconify-icon>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 bg-light-subtle d-flex align-items-center gap-3 p-3">
                                <div class="avatar-xs bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:shield-warning-linear"></iconify-icon>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-14">Policies & Terms</h6>
                                    <small class="text-muted">Vendor rules & regulations</small>
                                </div>
                                <iconify-icon icon="solar:alt-arrow-right-linear" class="ms-auto text-muted"></iconify-icon>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 bg-light-subtle d-flex align-items-center gap-3 p-3">
                                <div class="avatar-xs bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:question-square-linear"></iconify-icon>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-14">FAQs</h6>
                                    <small class="text-muted">Commonly asked questions</small>
                                </div>
                                <iconify-icon icon="solar:alt-arrow-right-linear" class="ms-auto text-muted"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: FAQ/Tickets Placeholder -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 p-4 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold">Frequently Asked Questions</h5>
                        <button class="btn btn-sm btn-soft-purple" style="color: #5d1a8f; background-color: #f3e8ff;">View All</button>
                    </div>
                    <div class="card-body p-4">
                        <div class="accordion accordion-flush" id="faqAccordion">
                            <div class="accordion-item border-0 border-bottom mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-dark py-3 px-0 bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        How do I track my daily sales?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-muted">
                                        You can track your daily sales directly from the Dashboard or by visiting the "Sales Reports" section in the sidebar. We provide real-time updates on your revenue, order counts, and transaction history.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 border-bottom mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-dark py-3 px-0 bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        When will I receive my payments?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-muted">
                                        Payments are typically processed every 15 days for all delivered orders. You can check your pending balance and upcoming payout dates in the "Finance" or "Withdrawals" section.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 border-bottom mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-dark py-3 px-0 bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        What should I do if an order is returned?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-muted">
                                        If an order is returned, you will receive a notification. The product will be returned to your store inventory, and the amount will be adjusted in your next payout cycle. Please refer to our return policy for more details.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-dark py-3 px-0 bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        How to update my store information?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-muted">
                                        Go to "My Profile" to update your store name, logo, contact details, and bank information. Some changes might require verification from our admin team before they reflect on the platform.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-light border d-flex align-items-center gap-4">
                            <div class="avatar-lg bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                                <iconify-icon icon="solar:chat-line-linear" class="fs-32 text-purple" style="color: #5d1a8f !important;"></iconify-icon>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Still need help?</h6>
                                <p class="text-muted mb-0 small">Create a support ticket and our team will get back to you shortly.</p>
                            </div>
                            <button class="btn btn-purple px-4" style="background-color: #5d1a8f; color: white;">Open Ticket</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
