@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Campaigns</h4>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="text-muted small me-3">
                                <i class="solar:clock-circle-bold-duotone align-middle me-1"></i>
                                Current Server Time: <span class="fw-medium">{{ now()->format('Y-m-d H:i') }}</span>
                            </div>
                            <a href="{{ route('campaign.add') }}" class="btn btn-primary btn-sm">Create Campaign</a>
                            <form method="POST" action="{{ route('campaign.close.all') }}" onsubmit="return confirm('Close all campaigns? This will set status to Closed for all.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">Close All</button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        @if(session('vendor_skip_warning'))
                            <div class="alert alert-warning">
                                {{ session('vendor_skip_warning') }}
                            </div>
                        @endif
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr class="text-nowrap">
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Offer</th>
                                    <th>Discount %</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Active</th>
                                    <th>Vendors</th>
                                    <th>Products</th>
                                    <th>Requests</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($campaigns->isEmpty())
                                <tr>
                                    <td colspan="12" class="text-center">No campaigns found</td>
                                </tr>
                                @else
                                @foreach($campaigns as $key => $c)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $c->name }}</td>
                                        <td>
                                            @if($c->offer)
                                                <span class="badge bg-info">{{ $c->offer->code }}</span>
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($c->discount_percent, 2) }}</td>
                                        <td>{{ $c->start_date ? $c->start_date->format('Y-m-d H:i') : 'N/A' }}</td>
                                        <td>{{ $c->end_date ? $c->end_date->format('Y-m-d H:i') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $c->status_badge }}">
                                                {{ $c->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input campaign-status-toggle" type="checkbox" role="switch" data-id="{{ $c->id }}" {{ $c->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>{{ $c->vendors_count ?? 0 }}</td>
                                        <td>{{ $c->products_count ?? 0 }}</td>
                                        <td>
                                              <div class="d-flex flex-column gap-1">
                                                <a class="btn btn-sm btn-primary hover-opacity-100" data-bs-toggle="tooltip"
                                                   href="{{ route('campaign.vendor.requests.page', ['id' => $c->id]) }}"
                                                   title="Vendor Requests">
                                                   Vendors
                                                </a>
                                                <a class="btn btn-sm btn-info hover-opacity-100" data-bs-toggle="tooltip"
                                                   href="{{ route('campaign.product.requests', ['id' => $c->id]) }}"
                                                   title="Product Requests">
                                                   Products
                                                </a>
                                              </div>
                                        </td>
                                        <td>
                                              <div class="d-flex gap-2">

                                                 <a href="javascript:void(0);" onclick="openEdit(this)"
                                                    data-id="{{ $c->id }}"
                                                    data-name="{{ $c->name }}"
                                                    data-discount="{{ number_format($c->discount_percent, 2, '.', '') }}"
                                                    data-start="{{ $c->start_date ? $c->start_date->format('Y-m-d\TH:i') : '' }}"
                                                    data-end="{{ $c->end_date ? $c->end_date->format('Y-m-d\TH:i') : '' }}"
                                                    data-status="{{ $c->status ? 1 : 0 }}" 
                                                    data-offer="{{ $c->offer_id }}"
                                                    data-budget="{{ $c->budget_per_vendor }}"
                                                    data-max="{{ $c->max_vendors }}"
                                                    class="hover-opacity-100" data-bs-toggle="tooltip" title="Edit">
                                                                    <iconify-icon icon="solar:pen-linear" class="align-middle fs-20"></iconify-icon>
                                                                </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                            </tbody>
                        </table>
                        <div class="mt-3 d-flex justify-content-end">
                            {{ $campaigns->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        @push('chart-scripts')
        <script>
            const csrf = '{{ csrf_token() }}';
            let editCampaignId = null;

            function openEdit(btn) {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const discount = btn.getAttribute('data-discount');
                const start = btn.getAttribute('data-start');
                const end = btn.getAttribute('data-end');
                const status = btn.getAttribute('data-status');
                const offerId = btn.getAttribute('data-offer');
                const budget = btn.getAttribute('data-budget');
                const max = btn.getAttribute('data-max');
                editCampaignId = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_discount_percent').value = discount;
                document.getElementById('edit_start_date').value = start;
                document.getElementById('edit_end_date').value = end;
                document.getElementById('edit_status').checked = status === '1';
                document.getElementById('edit_offer_id').value = offerId || '';
                document.getElementById('edit_budget_per_vendor').value = budget;
                document.getElementById('edit_max_vendors').value = max;
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            }

            function submitEdit() {
                if (!editCampaignId) return;
                const name = document.getElementById('edit_name').value;
                const discount = document.getElementById('edit_discount_percent').value;
                const start = document.getElementById('edit_start_date').value;
                const end = document.getElementById('edit_end_date').value;
                const status = document.getElementById('edit_status').checked ? 1 : 0;
                const offerId = document.getElementById('edit_offer_id').value;
                const budget = document.getElementById('edit_budget_per_vendor').value;
                const max = document.getElementById('edit_max_vendors').value;

                const saveBtn = document.getElementById('edit_save_btn');
                const originalHtml = saveBtn ? saveBtn.innerHTML : null;

                // Clear previous errors
                const modalBody = document.querySelector('#editModal .modal-body');
                modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                modalBody.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...';
                }

                fetch(`{{ url('update-campaign') }}/${editCampaignId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            name: name,
                            discount_percent: discount,
                            start_date: start,
                            end_date: end,
                            status: status,
                            offer_id: offerId || null,
                            budget_per_vendor: budget,
                            max_vendors: max
                        })
                    })
                    .then(async r => {
                        const json = await r.json();
                        if (!r.ok) {
                            if (r.status === 422) {
                                throw { type: 'validation', errors: json.errors };
                            }
                            throw new Error(json.message || 'Update failed');
                        }
                        return json;
                    })
                    .then(json => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: json.message || 'Campaign updated successfully',
                        }).then(function () {
                            location.reload();
                        });
                    })
                    .catch(err => {
                        if (err.type === 'validation') {
                            Object.keys(err.errors).forEach(key => {
                                const input = document.getElementById('edit_' + key);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'invalid-feedback';
                                    errorDiv.innerText = err.errors[key][0];
                                    input.after(errorDiv);
                                }
                            });
                            toastr.error('Please fix the validation errors.');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: err.message || 'Failed to update',
                            });
                        }
                    })
                    .finally(() => {
                        if (saveBtn && originalHtml !== null) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = originalHtml;
                        }
                    });
            }

            $(document).on('change', '.campaign-status-toggle', function() {
                const id = $(this).data('id');
                const status = $(this).prop('checked') ? 1 : 0;
               var text = status === 1 ? 'Active' : 'Inactive';
                $(this).next('span').text(text);
                $.ajax({
                    url: "{{ route('campaign.change.status') }}",
                    type: "POST",
                    data: {
                        _token: csrf,
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message || 'Failed to update status');
                        }   
                    },
                    error: function() {
                        toastr.error('Error updating campaign status');
                    }
                });
            });

           

       
        </script>
        @endpush

        @push('chart-scripts')
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Campaign</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount %</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="edit_discount_percent">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start</label>
                            <input type="datetime-local" class="form-control" id="edit_start_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End</label>
                            <input type="datetime-local" class="form-control" id="edit_end_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link Offer (Optional)</label>
                            <select class="form-select" id="edit_offer_id">
                                <option value="">-- None --</option>
                                @foreach($offers as $o)
                                    <option value="{{ $o->id }}">
                                        {{ $o->code }} ({{ $o->type == 1 ? 'Percent' : 'Flat' }} @ {{ $o->value }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Budget per Vendor (Optional)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="edit_budget_per_vendor">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Vendors (Optional)</label>
                            <input type="number" min="1" class="form-control" id="edit_max_vendors">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_status">
                            <label class="form-check-label" for="edit_status">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="edit_save_btn" onclick="submitEdit()">Save</button>
                    </div>
                </div>
            </div>
        </div>
        @endpush
