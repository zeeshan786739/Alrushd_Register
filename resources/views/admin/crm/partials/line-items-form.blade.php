<div class="card radius-12 shadow-2 border-0 mb-24" data-crm-line-items>
    <div class="card-body p-24">
        <div class="d-flex align-items-center justify-content-between gap-12 mb-20 flex-wrap">
            <h6 class="mb-0 fw-semibold">Line Items</h6>
            <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8" id="add-line-item" data-crm-add-line-item>
                <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add Item
            </button>
        </div>
        <div class="table-responsive">
            <table class="table crm-line-items-table" id="line-items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="width:120px">Qty</th>
                        <th style="width:140px">Unit Price</th>
                        <th style="width:120px">Total</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items ?? [['description'=>'','quantity'=>1,'unit_price'=>0]] as $index => $item)
                        <tr class="line-item-row">
                            <td><input type="text" name="items[{{ $index }}][description]" class="form-control radius-8" value="{{ $item['description'] ?? $item->description ?? '' }}" required></td>
                            <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control radius-8 item-qty" min="1" value="{{ $item['quantity'] ?? $item->quantity ?? 1 }}" required></td>
                            <td><input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control radius-8 item-price" min="0" value="{{ $item['unit_price'] ?? $item->unit_price ?? 0 }}" required></td>
                            <td class="item-total fw-semibold">{{ number_format((float) (($item['quantity'] ?? $item->quantity ?? 1) * ($item['unit_price'] ?? $item->unit_price ?? 0)), 2, '.', '') }}</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-line-item" data-crm-remove-line-item aria-label="Remove item">&times;</button></td>
                        </tr>
                    @empty
                        <tr class="line-item-row">
                            <td><input type="text" name="items[0][description]" class="form-control radius-8" required></td>
                            <td><input type="number" name="items[0][quantity]" class="form-control radius-8 item-qty" min="1" value="1" required></td>
                            <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control radius-8 item-price" min="0" value="0" required></td>
                            <td class="item-total fw-semibold">0.00</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-line-item" data-crm-remove-line-item aria-label="Remove item">&times;</button></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
