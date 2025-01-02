<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ReturnDetail;
use App\Traits\FileUploadTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DraftController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     // Retrieve the selected category from the session, default to 'daily' if none is set
    //     $selectedCategory = session('category', 'daily');

    //     // Fetch invoices based on the selected category
    //     $invoices = Invoice::with('customer')
    //         ->whereHas('category', function ($query) use ($selectedCategory) {
    //             $query->where('name', $selectedCategory);
    //         })
    //         ->get();

    //     // Pass the selected category to the view
    //     return view('invoices.index', compact('invoices', 'selectedCategory'));
    // }

    public function index(Request $request)
    {
        // Retrieve the selected category from the session, default to 'daily' if none is set
        $selectedCategory = session('category', 'daily');

        // Get the status filter from the request
        $status = $request->query('status');

        // Fetch invoices based on the selected category and optional status filter, excluding 'draft'
        $invoices = Invoice::with('customer')
            ->whereHas('category', function ($query) use ($selectedCategory) {
                $query->where('name', $selectedCategory);
            })
            ->where('status', '=', 'draft') // Exclude invoices with 'draft' status
            ->when($status === 'paid', function ($query) {
                $query->where('paid', true);
            })
            ->when($status === 'unpaid', function ($query) {
                $query->where('paid', false);
            })
            ->paginate(10); // Paginate results for better performance

        // Pass the selected category and status to the view
        return view('draft.index', compact('invoices', 'selectedCategory', 'status'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('draft.create', compact('customers', 'products'));
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
            'total_discount' => 'nullable|numeric|min:0',
            'paid' => 'required|in:0,1',
            'payment_method' => 'required|in:cash,credit_card', // Validate payment method
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

        // Retrieve the selected category from the session
        $categoryName = session('category', 'daily');
        $category = Category::where('name', $categoryName)->firstOrFail();

        // Create the Invoice
        $invoice = new Invoice([
            'customer_id' => $customer->id,
            'user_id' => auth()->user()->id,
            'category_id' => $category->id, // Associate the invoice with the selected category
            'rental_start_date' => $request->rental_start_date,
            'rental_end_date' => $request->rental_end_date,
            'total_discount' => $request->total_discount ?? 0,
            'amount_per_day' => $request->amount_per_day,
            'total_amount' => $request->total_amount,
            'paid' => $request->paid,
            'payment_method' => $request->payment_method, // Store payment method
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
        $invoice->items()->saveMany($invoiceItems);

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice created successfully');
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $invoice = Invoice::with('items.product', 'additionalItems', 'returnDetails')->findOrFail($id);

        $totals = $invoice->calculateTotals();

        return view('invoices.show', compact('invoice', 'totals'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
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
        $invoice->total_discount = $request->total_discount ?? 0;
        $invoice->paid = (bool) $request->paid;
        $invoice->status = 'active';

        // Recalculate subtotal, discount, and total for updated items
        $subtotal = 0;
        $invoice->items()->delete();
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

        // Calculate total and discount
        $discountAmount = ($subtotal * $invoice->total_discount) / 100;
        $invoice->total = $subtotal - $discountAmount;
        $invoice->save();

        // Attach updated items to the invoice
        $invoice->items()->saveMany($invoiceItems);

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
        $invoice = Invoice::with('items.product', 'additionalItems', 'returnDetails')->findOrFail($id);

        $totals = $invoice->calculateTotals();

        return view('invoices.print', compact('invoice', 'totals'));
    }


    /**
     * Download the invoice as PDF.
     */
    public function download($id)
    {
        $invoice = Invoice::with('items.product', 'additionalItems', 'returnDetails')->findOrFail($id);

        $totals = $invoice->calculateTotals();

        $pdf = Pdf::loadView('invoices.download', compact('invoice', 'totals'));

        return $pdf->download("invoice-{$invoice->id}.pdf");
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

                $item = $invoice->items->find($key);
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

                // Calculate the cost of the return
                $returnDate = Carbon::parse($validatedReturn['return_date']);
                $usedDays = $returnDate->diffInDays(Carbon::parse($invoice->rental_start_date));
                $usedCost = $item->price * $usedDays * $validatedReturn['quantity'];

                // Create the return record
                ReturnDetail::create([
                    'invoice_id' => $invoice->id,
                    'invoice_item_id' => $item->id,
                    'product_id' => $item->product_id, // Include the product_id here
                    'returned_quantity' => $validatedReturn['quantity'],
                    'days_used' => $usedDays,
                    'cost' => $usedCost,
                    'return_date' => $validatedReturn['return_date'],
                ]);

                // Update the item's returned quantity
                $item->increment('returned_quantity', $validatedReturn['quantity']);

                // Update the invoice total amount
                $invoice->decrement('total_amount', $usedCost);
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

    public function updatePaymentStatus(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $validated = $request->validate([
            'paid' => 'required|boolean',
        ]);

        $invoice->update(['paid' => $validated['paid']]);

        return redirect()->route('invoices.edit', $id)->with('success', 'Payment status updated successfully.');
    }
}
