@foreach($cat_data as $category)
<tr id="row_{{ $category->id }}">
    <td class="ps-4">
        <div class="form-check">
            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $category->id }}">
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center gap-3">
            <div class="p-1">
                <img src="{{ $category->image }}" alt="" class="img-fluid" style="width: 40px; height: 40px; object-fit: cover;">
            </div>
            <div>
                <h5 class="fs-14 mb-0 fw-semibold">{{ $category->name }}</h5>
                <p class="text-muted mb-0 fs-12 text-truncate" style="max-width: 150px;">{{ $category->description ?: 'No description' }}</p>
            </div>
        </div>
    </td>
    <td>#{{ str_pad($category->id, 6, '0', STR_PAD_LEFT) }}</td>
    <td>{{ $category->products_count }}</td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                data-id="{{ $category->id }}" 
                data-url="{{ route('change.category.status') }}"
                {{ $category->is_active ? 'checked' : '' }}>
        </div>
    </td>
    <td class="text-end pe-4">
        <div class="d-flex align-items-center justify-content-end gap-2">
            
            <a href="{{ url('edit-category', $category->slug) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit">
                <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
            </a>
            <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-category" data-id="{{ $category->id }}" data-bs-toggle="tooltip" title="Delete">
                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
            </a>
        </div>
    </td>
</tr>
@endforeach
