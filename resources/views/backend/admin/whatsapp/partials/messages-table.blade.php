@forelse($messages as $m)
<tr>
    <td>{{ $loop->iteration + ($messages->currentPage() - 1) * $messages->perPage() }}</td>
    <td>{{ $m->recipient }}</td>
    <td><small>{{ Str::limit($m->message, 60) }}</small></td>
    <td><small>{{ $m->template_name ?? '—' }}</small></td>
    <td>
        @if($m->order)
        <a href="{{ route('orders.details', $m->order->order_reference_id) }}" class="text-decoration-none">#{{ $m->order->order_reference_id }}</a>
        @else
        —
        @endif
    </td>
    <td>
        @php
        $cls = match($m->status) { 'sent' => 'success', 'failed' => 'danger', default => 'warning' };
        @endphp
        <span class="badge bg-{{ $cls }}">{{ ucfirst($m->status) }}</span>
        @if($m->error_message)
        <br><small class="text-danger" title="{{ $m->error_message }}">{{ Str::limit($m->error_message, 30) }}</small>
        @endif
    </td>
    <td><small class="text-muted">{{ $m->sent_at ? $m->sent_at->format('d M Y H:i') : '—' }}</small></td>
</tr>
@empty
<tr><td colspan="7" class="text-center py-4 text-muted">No messages sent yet</td></tr>
@endforelse
