<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ReturnDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('customer')->get();
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255|required_without:customer_id',
            'customer_phone' => 'nullable|string|max:255|required_without:customer_id',
            'customer_address' => 'nullable|string|max:255|required_without:customer_id',
            'rental_start_date' => 'required|date',
            'rental_end_date' => 'required|date|after_or_equal:rental_start_date',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'integer|min:1',
            'prices' => 'required|array|min:1',
            'prices.*' => 'numeric|min:0',
            'total_vat' => 'nullable|numeric|min:0',
            'total_discount' => 'nullable|numeric|min:0',
            'paid' => 'required|in:0,1',
            'amount_per_day' => 'required|numeric', // Validating amount per day from the form
            'days' => 'required|integer', // Validating days from the form
            'total_amount' => 'required|numeric' // Validating total from the form
        ]);

        // Handle Customer
        if ($request->filled('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        } else {
            $customer = Customer::create([
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'address' => $request->customer_address,
            ]);
        }

        // Create the Invoice
        $invoice = new Invoice([
            'customer_id' => $customer->id,
            'user_id' => auth()->user()->id,
            'rental_start_date' => $request->rental_start_date,
            'rental_end_date' => $request->rental_end_date,
            'total_vat' => $request->total_vat ?? 0,
            'total_discount' => $request->total_discount ?? 0,
            'amount_per_day' => $request->amount_per_day,
            'total_amount' => $request->total_amount,
            'paid' => $request->paid,
            'status' => 'active',
            'days' => $request->days,
        ]);
        $invoice->save();

        // Calculate and Add Invoice Items
        $rentalStartDate = Carbon::parse($request->rental_start_date);
        $rentalEndDate = Carbon::parse($request->rental_end_date);
        $rentalDays = $rentalStartDate->diffInDays($rentalEndDate);

        $invoiceItems = [];
        foreach ($request->products as $index => $product_id) {
            $quantity = $request->quantities[$index];
            $price = $request->prices[$index];
            $totalPrice = $quantity * $price * $rentalDays;

            $invoiceItems[] = new InvoiceItem([
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total_price' => $totalPrice,
                'rental_start_date' => $request->rental_start_date,
                'rental_end_date' => $request->rental_end_date,
                'days' => $rentalDays,
                'returned_quantity' => 0, // Initial value
                'added_quantity' => 0, // Initial value
            ]);
        }

        // Attach items to the invoice
        $invoice->invoiceItems()->saveMany($invoiceItems);

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice created successfully');
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);

        // Calculate subtotal, VAT, discount, and total
        $subtotal = $invoice->invoiceItems->sum(fn($item) => $item->quantity * $item->price);
        $vatTotal = ($subtotal * $invoice->total_vat) / 100;
        $discountTotal = ($subtotal * $invoice->total_discount) / 100;
        $total = $subtotal + $vatTotal - $discountTotal;

        return view('invoices.show', compact('invoice', 'subtotal', 'vatTotal', 'discountTotal', 'total'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $invoice = Invoice::with('invoiceItems')->findOrFail($id);
        $customers = Customer::all();
        $products = Product::all();

        return view('invoices.edit', compact('invoice', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'integer|min:1',
            'prices' => 'required|array|min:1',
            'prices.*' => 'numeric|min:0',
            'total_vat' => 'nullable|numeric|min:0',
            'total_discount' => 'nullable|numeric|min:0',
            'paid' => 'required|in:0,1',
        ]);

        $invoice = Invoice::findOrFail($id);

        // Update customer if a new customer_id is provided
        if ($request->filled('customer_id') && $request->customer_id != $invoice->customer_id) {
            $invoice->customer_id = $request->customer_id;
        }

        // Calculate rental days for updated items
        $rentalStartDate = Carbon::parse($request->rental_start_date);
        $rentalEndDate = Carbon::parse($request->rental_end_date);
        $rentalDays = $rentalStartDate->diffInDays($rentalEndDate);

        // Update invoice details
        $invoice->total_vat = $request->total_vat ?? 0;
        $invoice->total_discount = $request->total_discount ?? 0;
        $invoice->paid = (bool) $request->paid;
        $invoice->status = 'active';

        // Recalculate subtotal, VAT, discount, and total for updated items
        $subtotal = 0;
        $invoice->invoiceItems()->delete();
        $invoiceItems = [];

        foreach ($request->products as $index => $product_id) {
            $quantity = $request->quantities[$index];
            $price = $request->prices[$index];
            $totalPrice = $quantity * $price * $rentalDays;
            $subtotal += $totalPrice;

            $invoiceItems[] = new InvoiceItem([
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total_price' => $totalPrice,
            ]);
        }

        // Calculate total with VAT and discount
        $vatAmount = ($subtotal * $invoice->total_vat) / 100;
        $discountAmount = ($subtotal * $invoice->total_discount) / 100;
        $invoice->total = $subtotal + $vatAmount - $discountAmount;
        $invoice->save();

        // Attach updated items to the invoice
        $invoice->invoiceItems()->saveMany($invoiceItems);

        return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->delete();

            return response()->json(['status' => 'success', 'message' => 'Deleted Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong!']);
        }
    }

    public function print($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);

        // Initialize totals
        $subtotal = 0;
        $discountTotal = 0;
        $vatTotal = 0;
        $total = 0;

        foreach ($invoice->invoiceItems as $item) {
            $itemSubtotal = $item->quantity * $item->price;
            $itemVat = ($itemSubtotal * $item->vat) / 100;
            $itemDiscount = ($itemSubtotal * $item->discount) / 100;
            $itemTotal = $itemSubtotal + $itemVat - $itemDiscount;

            $subtotal += $itemSubtotal;
            $discountTotal += $itemDiscount;
            $vatTotal += $itemVat;
            $total += $itemTotal;
        }

        return view('invoices.print', compact('invoice', 'subtotal', 'discountTotal', 'vatTotal', 'total'));
    }

    /**
     * Download the invoice as PDF.
     */
    public function download($id)
    {
        // Fetch the invoice with related data
        $invoice = Invoice::with(['items.product', 'additionalItems.product', 'returnDetails.invoiceItem.product'])->findOrFail($id);

        // Initialize totals
        $subtotal = 0;
        $discountTotal = 0;
        $vatTotal = 0;
        $total = 0;

        // Calculate totals for items
        foreach ($invoice->invoiceItems as $item) {
            $itemSubtotal = $item->price * $item->quantity;
            $itemVat = ($itemSubtotal * $invoice->total_vat) / 100;
            $itemDiscount = ($itemSubtotal * $invoice->total_discount) / 100;
            $itemTotal = $itemSubtotal + $itemVat - $itemDiscount;

            $subtotal += $itemSubtotal;
            $discountTotal += $itemDiscount;
            $vatTotal += $itemVat;
            $total += $itemTotal;
        }

        // Add additional items to totals
        foreach ($invoice->additionalItems as $addedItem) {
            $subtotal += $addedItem->total_price;
            $total += $addedItem->total_price; // VAT/discounts are assumed applied at the item level
        }

        // Subtract returned items from totals
        foreach ($invoice->returnDetails as $return) {
            $total -= $return->cost;
        }

        // Generate the PDF using the updated Blade view
        $pdf = Pdf::loadView('invoices.download', compact('invoice', 'subtotal', 'discountTotal', 'vatTotal', 'total'));

        // Return the PDF download response
        return $pdf->download('invoice-' . $invoice->id . '.pdf');
    }


    public function processReturns(Request $request, $invoiceId)
    {
        $invoice = Invoice::with('items.product')->findOrFail($invoiceId);

        $validated = $request->validate([
            'returns' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->returns as $key => $return) {
                // Skip unselected items
                if (!isset($return['selected'])) {
                    continue;
                }

                $item = $invoice->invoiceItems->find($key);
                if (!$item) {
                    return redirect()->back()->withErrors([
                        "returns.{$key}.item_id" => "The selected item is invalid.",
                    ]);
                }

                // Validate return fields
                $validatedReturn = Validator::make($return, [
                    'quantity' => 'required|integer|min:1|max:' . ($item->quantity - $item->returned_quantity),
                    'return_date' => 'required|date|after_or_equal:' . $invoice->rental_start_date . '|before_or_equal:' . $invoice->rental_end_date,
                ])->validate();

                $returnDate = Carbon::parse($validatedReturn['return_date']);
                $usedDays = $returnDate->diffInDays(Carbon::parse($invoice->rental_start_date));
                $usedCost = $item->price * $usedDays * $validatedReturn['quantity'];

                // Save return details with invoice_id
                ReturnDetail::create([
                    'invoice_id' => $invoice->id, // Provide the invoice ID
                    'invoice_item_id' => $item->id,
                    'returned_quantity' => $validatedReturn['quantity'],
                    'days_used' => $usedDays,
                    'cost' => $usedCost,
                    'return_date' => $validatedReturn['return_date'],
                ]);

                // Update returned quantity
                $item->returned_quantity += $validatedReturn['quantity'];
                $item->save();

                // Update invoice total
                $invoice->total_amount -= $usedCost;
            }

            $invoice->save();
            DB::commit();

            return redirect()->route('invoices.show', $invoiceId)->with('success', 'Returns processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process returns: ' . $e->getMessage());
        }
    }


    public function addItems(Request $request, $invoiceId)
    {
        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::findOrFail($invoiceId);

        DB::beginTransaction();

        try {
            foreach ($validated['products'] as $product) {
                $productId = $product['product_id'];
                $quantity = $product['quantity'];
                $price = $product['price'];

                // Ensure the `added_date` is set to the current date
                $addedDate = now();

                // Calculate the number of days from the `added_date` to the `rental_end_date`
                $rentalEndDate = Carbon::parse($invoice->rental_end_date);
                $days = max($addedDate->diffInDays($rentalEndDate), 1);

                // Calculate total price for the new items
                $totalPrice = $price * $quantity * $days;

                // Add the new additional item
                $invoice->additionalItems()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total_price' => $totalPrice,
                    'added_date' => $addedDate,
                ]);

                // Update the invoice total
                $invoice->total_amount += $totalPrice;
            }

            $invoice->save();
            DB::commit();

            return redirect()->route('invoices.show', $invoiceId)->with('success', 'Items added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add items: ' . $e->getMessage());
        }
    }



}
