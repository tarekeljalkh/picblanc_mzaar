<form action="{{ route('invoices.add-dates', $invoice->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="invoice_start_date" class="form-label">Invoice Start Date</label>
            <input type="date" name="invoice_start_date" id="invoice_start_date"
                class="form-control" value="{{ old('invoice_start_date', $invoice->rental_start_date) }}" required>
        </div>
        {{ $invoice->rental_start_date }}
        <div class="col-md-6">
            <label for="invoice_end_date" class="form-label">Invoice End Date</label>
            <input type="date" name="invoice_end_date" id="invoice_end_date"
                class="form-control" value="{{ old('invoice_end_date', $invoice->end_date) }}" required>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Update Dates</button>
    </div>
</form>
