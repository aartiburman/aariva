@extends('backend.layouts.app')

@section('content')
<div class="page-content">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manage Products for Campaign: {{ $campaign->name }}</h4>
                    <a href="{{ route('vendor.campaigns') }}" class="btn btn-secondary btn-sm">Back to Campaigns</a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Current Campaign Products</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($campaignProducts as $cp)
                                        <tr>
                                            <td>{{ $cp->name }}</td>
                                            <td>
                                                <span class="badge {{ $cp->campaign_status == 1 ? 'bg-success' : ($cp->campaign_status == 2 ? 'bg-danger' : 'bg-warning') }}">
                                                    {{ $cp->campaign_status == 1 ? 'Approved' : ($cp->campaign_status == 2 ? 'Rejected' : 'Pending') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No products added yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Add Products to Campaign</h5>
                            <form action="{{ route('vendor.campaign.add.products', $campaign->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Select Products</label>
                                    <select name="product_ids[]" class="form-control js-choice" multiple required>
                                        @foreach($availableProducts as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (SKU: {{ $product->sku }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Only approved & active products not in other campaigns are listed.</small>
                                </div>
                                <button type="submit" class="btn btn-primary px-4" style="background-color: #4b38b3; border-color: #4b38b3;">
                                    <iconify-icon icon="solar:plus-circle-linear" class="align-middle me-1"></iconify-icon> Submit for Approval
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const element = document.querySelector('.js-choice');
    if (element) {
        new Choices(element, {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: 'Select products...',
            searchEnabled: true,
            shouldSort: false,
        });
    }
});
</script>
@endpush
