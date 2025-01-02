<form action="{{ route('invoices.updateInvoiceStatus', $invoice->id) }}" method="POST">
    @csrf
    @method('PATCH')

    <!-- Status Field -->
    <div class="mb-3">
        <label for="status" class="form-label">Invoice Status</label>
        <select id="status" name="status" class="form-select">
            <option value="returned" {{ $invoice->status === 'returned' ? 'selected' : '' }}>Returned</option>
            <option value="overdue" {{ $invoice->status === 'overdue' ? 'selected' : '' }}>Overdue</option>
        </select>
    </div>

    <!-- Update Button -->
    <button type="submit" class="btn btn-success">Update Status</button>
</form>
