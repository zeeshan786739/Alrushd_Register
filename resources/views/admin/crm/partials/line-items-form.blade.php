<div class="card radius-12 shadow-2 border-0 mb-24">
    <div class="card-body p-24">
        <div class="d-flex align-items-center justify-content-between gap-12 mb-20 flex-wrap">
            <h6 class="mb-0 fw-semibold">Line Items</h6>
            <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8" id="add-line-item">
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
                            <td class="item-total fw-semibold">{{ number_format(($item['quantity'] ?? $item->quantity ?? 1) * ($item['unit_price'] ?? $item->unit_price ?? 0), 2) }}</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-line-item">&times;</button></td>
                        </tr>
                    @empty
                        <tr class="line-item-row">
                            <td><input type="text" name="items[0][description]" class="form-control radius-8" required></td>
                            <td><input type="number" name="items[0][quantity]" class="form-control radius-8 item-qty" min="1" value="1" required></td>
                            <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control radius-8 item-price" min="0" value="0" required></td>
                            <td class="item-total fw-semibold">0.00</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-line-item">&times;</button></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('line-items-table');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    let rowIndex = tbody.querySelectorAll('.line-item-row').length;

    function recalcRow(row) {
        const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        const totalCell = row.querySelector('.item-total');
        if (totalCell) totalCell.textContent = (qty * price).toFixed(2);
    }

    document.getElementById('add-line-item')?.addEventListener('click', function () {
        const row = document.createElement('tr');
        row.className = 'line-item-row';
        row.innerHTML = `
            <td><input type="text" name="items[${rowIndex}][description]" class="form-control radius-8" required></td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control radius-8 item-qty" min="1" value="1" required></td>
            <td><input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control radius-8 item-price" min="0" value="0" required></td>
            <td class="item-total fw-semibold">0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-line-item">&times;</button></td>`;
        tbody.appendChild(row);
        rowIndex++;
    });

    table.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-line-item')) {
            const rows = tbody.querySelectorAll('.line-item-row');
            if (rows.length > 1) e.target.closest('tr').remove();
        }
    });

    table.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
            recalcRow(e.target.closest('tr'));
        }
    });
});
</script>
