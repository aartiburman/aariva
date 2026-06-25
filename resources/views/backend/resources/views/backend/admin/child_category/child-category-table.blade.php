@foreach($childCategories as $child)
<tr id="row_{{ $child->id }}">
    <td class="ps-4">
        <div class="form-check">
            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $child->id }}">
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center gap-3">
            <div class="p-1">
                <img src="{{ $child->image }}" alt="" class="img-fluid" style="width: 40px; height: 40px; object-fit: cover;">
            </div>
            <div>
                <h5 class="fs-14 mb-0 fw-semibold">{{ $child->name }}</h5>
                <p class="text-muted mb-0 fs-12 text-truncate" style="max-width: 150px;">{{ $child->description ?: 'No description' }}</p>
            </div>
        </div>
    </td>
    <td>
        <span class="badge bg-primary-subtle text-primary border fw-medium">{{ $child->sub_categories_name }}</span>
    </td>
    <td>
        <span class="badge bg-info-subtle text-info border fw-medium">{{ $child->category_name }}</span>
    </td>
    <td>#{{ str_pad($child->id, 6, '0', STR_PAD_LEFT) }}</td>
    <td>{{ $child->products_count }}</td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                data-id="{{ $child->id }}" 
                data-url="{{ route('change.child.category.status') }}"
                {{ $child->is_active ? 'checked' : '' }}>
        </div>
    </td>
    <td class="text-end pe-4">
        <div class="d-flex align-items-center justify-content-end gap-2">
           
            <a href="{{ route('edit.child.category', $child->slug) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit">
                <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
            </a>
            <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-child-category" data-id="{{ $child->id }}" data-bs-toggle="tooltip" title="Delete">
                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
            </a>
        </div>
    </td>
</tr>
@endforeach