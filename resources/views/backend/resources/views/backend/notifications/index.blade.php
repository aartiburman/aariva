@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">All Notifications</h4>
                        <a href="{{ route('notifications.markAllRead') }}" class="btn btn-sm btn-outline-primary">Mark All as Read</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Notification</th>
                                        <th>Date</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications as $notification)
                                    <tr class="{{ $notification->read_at ? '' : ' fw-bold' }}">
                                        <td>
                                            @if($notification->read_at )
                                            <span class="badge bg-secondary">Read</span>
                                            @else
                                            <span class="badge bg-primary">Unread</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span class="avatar-title bg-soft-{{ $notification->data['priority'] == 'critical' ? 'danger' : ($notification->data['priority'] == 'medium' ? 'warning' : 'info') }} text-{{ $notification->data['priority'] == 'critical' ? 'danger' : ($notification->data['priority'] == 'medium' ? 'warning' : 'info') }} fs-20 rounded-circle">
                                                        <iconify-icon icon="{{ $notification->data['icon'] ?? 'solar:bell-linear' }}"></iconify-icon>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">
                                                        @if(isset($notification->data['url']))
                                                        <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="text-reset">
                                                            {{ $notification->data['title'] ?? 'Notification' }}
                                                        </a>
                                                        @else
                                                        {{ $notification->data['title'] ?? 'Notification' }}
                                                        @endif
                                                    </h6>
                                                    <p class="text-muted mb-0">{{ $notification->data['message'] ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $notification->created_at->diffForHumans() }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                @if(!$notification->read_at)
                                                <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Mark as Read">
                                                    <iconify-icon icon="solar:check-read-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                @endif
                                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline-block delete-notification-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="border-0 bg-transparent p-0 text-purple hover-opacity-100 delete-notification-btn" data-bs-toggle="tooltip" title="Delete">
                                                        <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No notifications found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $notifications->links() }}
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
    $(document).ready(function() {
        $('.delete-notification-btn').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this notification?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush