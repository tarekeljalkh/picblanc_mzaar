<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\AdditionalItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\ReturnDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $selectedCategory = session('category', 'daily');
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $hasDateFilter = $startDate && $endDate;

        // ✅ Convert to Carbon Dates only if provided
        if ($hasDateFilter) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        // ✅ Fetch Invoices with Eager Loading
        $invoices = Invoice::with([
            'customer',
            'invoiceItems',
            'customItems',
            'additionalItems',
            'payments',
            'category',
            'returnDetails.invoiceItem',
            'returnDetails.additionalItem',
            'returnDetails.customItem',
        ])
            ->whereHas('category', function ($query) use ($selectedCategory) {
                $query->where('name', $selectedCategory);
            });

        // ✅ Apply Date Filtering Based on Category Type
        if ($hasDateFilter) {
            if ($selectedCategory === 'season') {
                $invoices->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $invoices->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('rental_start_date', [$startDate, $endDate])
                        ->orWhereBetween('rental_end_date', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('rental_start_date', '<=', $endDate)
                                ->where('rental_end_date', '>=', $startDate)
                                ->where(function ($q) use ($startDate, $endDate) {
                                    $q->where('rental_start_date', '>=', $startDate)
                                        ->where('rental_end_date', '<=', $endDate);
                                });
                        });
                });
            }
        }

        // ✅ Fetch Filtered Invoices
        $invoices = $invoices->get();

        // ✅ Apply Payment Status Filtering Using Accessor
        if ($paymentStatus) {
            $invoices = $invoices->filter(function ($invoice) use ($paymentStatus) {
                return $invoice->payment_status === $paymentStatus;
            })->values(); // Reset keys
        }

        // ✅ Apply Returned / Not Returned Status Filtering
        if ($status === 'returned') {
            $invoices = $invoices->filter(fn($invoice) => $invoice->returned);
        } elseif ($status === 'not_returned') {
            $invoices = $invoices->filter(fn($invoice) => !$invoice->returned);
        }

        return view('invoices.index', compact(
            'invoices',
            'selectedCategory',
            'status',
            'paymentStatus',
            'startDate',
            'endDate'
        ));
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
        $categoryName = session('category', 'daily');

        // Validation rules
        $rules = [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255|required_without:customer_id',
            'customer_phone' => 'nullable|string|max:255|required_without:customer_id',
            'customer_address' => 'nullable|string|max:255|required_without:customer_id',
            'products' => 'nullable|array',
            'products.*' => 'nullable|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'integer|min:1',
            'prices' => 'required|array|min:1',
            'prices.*' => 'numeric|min:0',
            'custom_items' => 'nullable|array',
            'custom_items.*.name' => 'required|string|max:255',
            'custom_items.*.description' => 'nullable|string|max:255',
            'custom_items.*.price' => 'required|numeric|min:0',
            'custom_items.*.quantity' => 'required|integer|min:1',
            'total_discount' => 'nullable|numeric|min:0|max:100',
            'deposit' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,credit_card',
            'note' => 'nullable',
        ];

        if ($categoryName === 'daily') {
            $rules['rental_start_date'] = 'required|date';
            $rules['rental_end_date'] = 'required|date|after_or_equal:rental_start_date';
            $rules['days'] = 'required|integer|min:1';
        }

        try {
            // Validation
            $validated = $request->validate($rules);

            DB::beginTransaction();

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

            // Retrieve Category
            $category = Category::where('name', $categoryName)->firstOrFail();

            $subtotal = 0;
            $invoiceItems = [];
            $customItems = [];

            // Process Products
            if (!empty($request->products)) {
                foreach ($request->products as $index => $productId) {
                    if (!empty($productId)) {
                        $product = Product::findOrFail($productId);
                        $quantity = $request->quantities[$index];
                        $price = $request->prices[$index];

                        $totalPrice = ($categoryName === 'daily')
                            ? $quantity * $price * $request->days
                            : $quantity * $price;

                        $invoiceItems[] = new InvoiceItem([
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'price' => $price,
                            'total_price' => $totalPrice,
                            'rental_start_date' => $categoryName === 'daily' ? $request->rental_start_date : null,
                            'rental_end_date' => $categoryName === 'daily' ? $request->rental_end_date : null,
                            'days' => $categoryName === 'daily' ? $request->days : null,
                            'returned_quantity' => 0,
                            'added_quantity' => 0,
                        ]);

                        $subtotal += $totalPrice;
                    }
                }
            }

            // Process Custom Items
            if (!empty($request->custom_items)) {
                foreach ($request->custom_items as $customItem) {
                    $customItems[] = new CustomItem([
                        'name' => $customItem['name'],
                        'description' => $customItem['description'] ?? '',
                        'price' => $customItem['price'],
                        'quantity' => $customItem['quantity'],
                    ]);

                    $subtotal += $customItem['price'] * $customItem['quantity'];
                }
            }

            // Calculate VAT and Discount
            $totalVat = $subtotal * ($request->total_discount ?? 0) / 100;
            $totalDiscount = $subtotal * ($request->total_discount ?? 0) / 100;

            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'category_id' => $category->id,
                'total_vat' => $totalVat,
                'total_discount' => $totalDiscount,
                'deposit' => $request->deposit ?? 0,
                'status' => 'active',  // You can adjust this based on the status
                'rental_start_date' => $categoryName === 'daily' ? $request->rental_start_date : null,
                'rental_end_date' => $categoryName === 'daily' ? $request->rental_end_date : null,
                'days' => $categoryName === 'daily' ? $request->days : null,
                'note' => $request->note,
                'user_id' => auth()->id(), // Assuming the user is authenticated
            ]);

            // Attach items to invoice
            $invoice->invoiceItems()->saveMany($invoiceItems);
            $invoice->customItems()->saveMany($customItems);

            // Create payment record if payment is made
            if ($request->filled('payment_amount') && $request->payment_amount > 0) {
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $request->payment_amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            //  Log::error('Store Invoice Exception:', [
            //      'message' => $e->getMessage(),
            //      'line' => $e->getLine(),
            //      'file' => $e->getFile(),
            //  ]);
            return redirect()->back()->with('error', 'An error occurred while creating the invoice.');
        }
    }
    //  public function store(Request $request)
    //  {
    //      $categoryName = session('category', 'daily');

    //      // Validation rules
    //      $rules = [
    //          'customer_id' => 'nullable|exists:customers,id',
    //          'customer_name' => 'nullable|string|max:255|required_without:customer_id',
    //          'customer_phone' => 'nullable|string|max:255|required_without:customer_id',
    //          'customer_address' => 'nullable|string|max:255|required_without:customer_id',
    //          'products' => 'nullable|array',
    //          'products.*' => 'nullable|exists:products,id',
    //          'quantities' => 'required|array|min:1',
    //          'quantities.*' => 'integer|min:1',
    //          'prices' => 'required|array|min:1',
    //          'prices.*' => 'numeric|min:0',
    //          'custom_items' => 'nullable|array',
    //          'custom_items.*.name' => 'required|string|max:255',
    //          'custom_items.*.description' => 'nullable|string|max:255',
    //          'custom_items.*.price' => 'required|numeric|min:0',
    //          'custom_items.*.quantity' => 'required|integer|min:1',
    //          'total_discount' => 'nullable|numeric|min:0|max:100',
    //          'deposit' => 'nullable|numeric|min:0',
    //          'payment_amount' => 'nullable|numeric|min:0',
    //          'payment_method' => 'required|in:cash,credit_card',
    //          'note' => 'nullable',
    //      ];

    //      if ($categoryName === 'daily') {
    //          $rules['rental_start_date'] = 'required|date';
    //          $rules['rental_end_date'] = 'required|date|after_or_equal:rental_start_date';
    //          $rules['days'] = 'required|integer|min:1';
    //      }

    //      try {
    //          // Validation
    //          $validated = $request->validate($rules);
    //          //Log::info('Validation passed:', $validated);

    //          DB::beginTransaction();

    //          // Handle Customer
    //          if ($request->filled('customer_id')) {
    //              $customer = Customer::findOrFail($request->customer_id);
    //              //Log::info('Customer fetched:', ['id' => $customer->id, 'name' => $customer->name]);
    //          } else {
    //              $customer = Customer::create([
    //                  'name' => $request->customer_name,
    //                  'phone' => $request->customer_phone,
    //                  'address' => $request->customer_address,
    //              ]);
    //              //Log::info('Customer created:', $customer->toArray());
    //          }

    //          // Retrieve Category
    //          $category = Category::where('name', $categoryName)->firstOrFail();
    //          //Log::info('Category retrieved:', $category->toArray());

    //          $subtotal = 0;
    //          $invoiceItems = [];
    //          $customItems = [];

    //          // Process Products
    //          if (!empty($request->products)) {
    //              foreach ($request->products as $index => $productId) {
    //                  if (!empty($productId)) {
    //                      $product = Product::findOrFail($productId);
    //                      $quantity = $request->quantities[$index];
    //                      $price = $request->prices[$index];

    //                      $totalPrice = ($categoryName === 'daily')
    //                          ? $quantity * $price * $request->days
    //                          : $quantity * $price;

    //                      $invoiceItems[] = new InvoiceItem([
    //                          'product_id' => $product->id,
    //                          'quantity' => $quantity,
    //                          'price' => $price,
    //                          'total_price' => $totalPrice,
    //                          'rental_start_date' => $categoryName === 'daily' ? $request->rental_start_date : null,
    //                          'rental_end_date' => $categoryName === 'daily' ? $request->rental_end_date : null,
    //                          'days' => $categoryName === 'daily' ? $request->days : null,
    //                          'returned_quantity' => 0,
    //                          'added_quantity' => 0,
    //                      ]);

    //                      //Log::info('Processed product:', ['product_id' => $productId, 'quantity' => $quantity, 'price' => $price, 'total_price' => $totalPrice]);

    //                      $subtotal += $totalPrice;
    //                  }
    //              }
    //          }

    //          // Process Custom Items
    //          if (!empty($request->custom_items)) {
    //              foreach ($request->custom_items as $customItem) {
    //                  $customItems[] = new CustomItem([
    //                      'name' => $customItem['name'],
    //                      'description' => $customItem['description'] ?? '',
    //                      'price' => $customItem['price'],
    //                      'quantity' => $customItem['quantity'],
    //                  ]);

    //                  //Log::info('Processed custom item:', $customItem);

    //                  $subtotal += $customItem['price'] * $customItem['quantity'];
    //              }
    //          }

    //          $totalDiscount = $request->total_discount ?? 0;
    //          $discountAmount = ($subtotal * $totalDiscount) / 100;
    //          $totalAmount = $subtotal - $discountAmount;
    //          $deposit = $request->deposit ?? 0;
    //          $paymentAmount = $request->payment_amount ?? 0;

    //          $invoiceData = [
    //              'customer_id' => $customer->id,
    //              'user_id' => auth()->user()->id,
    //              'category_id' => $category->id,
    //              'total_discount' => $totalDiscount,
    //              'deposit' => $deposit,
    //              'total_amount' => $totalAmount,
    //              'paid_amount' => $paymentAmount,
    //              'payment_method' => $request->payment_method,
    //              'note' => $request->note,
    //          ];

    //          if ($categoryName === 'daily') {
    //              $invoiceData['rental_start_date'] = $request->rental_start_date;
    //              $invoiceData['rental_end_date'] = $request->rental_end_date;
    //              $invoiceData['days'] = $request->days;
    //          }

    //          //Log::info('Invoice data:', $invoiceData);

    //          $invoice = Invoice::create($invoiceData);

    //          if (!empty($invoiceItems)) {
    //              $invoice->invoiceItems()->saveMany($invoiceItems);
    //              //Log::info('Invoice items saved:', $invoiceItems);
    //          }

    //          if (!empty($customItems)) {
    //              foreach ($customItems as $item) {
    //                  $item->invoice_id = $invoice->id;
    //                  $item->save();
    //                  //Log::info('Custom item saved:', $item->toArray());
    //              }
    //          }

    //          DB::commit();

    //          return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice created successfully');
    //      } catch (\Illuminate\Validation\ValidationException $e) {
    //          //Log::error('Validation Exception:', $e->errors());
    //          return redirect()->back()->withErrors($e->errors())->withInput();
    //      } catch (\Exception $e) {
    //          DB::rollBack();
    //         //  Log::error('Store Invoice Exception:', [
    //         //      'message' => $e->getMessage(),
    //         //      'line' => $e->getLine(),
    //         //      'file' => $e->getFile(),
    //         //  ]);
    //          return redirect()->back()->with('error', 'An error occurred while creating the invoice.');
    //      }
    //  }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'invoiceItems.product',
            'additionalItems',
            'customItems',
            'returnDetails.invoiceItem.product',
            'returnDetails.additionalItem.product',
            'payments', // make sure payments are eager loaded too
        ])->findOrFail($id);

        $totals = $invoice->calculateTotals();
        return view('invoices.show', compact('invoice', 'totals'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $invoice = Invoice::with(['invoiceItems', 'additionalItems', 'returnDetails'])->findOrFail($id);
        $customers = Customer::all();
        $categoryName = session('category', 'daily'); // Get category from session

        // Fetch only products that belong to the selected category
        $products = Product::whereHas('category', function ($query) use ($categoryName) {
            $query->where('name', $categoryName);
        })->get();

        // Determine if the mode is seasonal
        $isSeasonal = $categoryName === 'season';

        // Calculate totals
        $totals = $invoice->calculateTotals();

        return view('invoices.edit', compact('invoice', 'customers', 'products', 'isSeasonal', 'totals'));
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

        // Calculate total and discount
        $discountAmount = ($subtotal * $invoice->total_discount) / 100;
        $invoice->total = $subtotal - $discountAmount;
        $invoice->save();

        // Attach updated items to the invoice
        $invoice->invoiceItems()->saveMany($invoiceItems);

        return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
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
        $invoice = Invoice::with([
            'invoiceItems.product',
            'additionalItems',
            'returnDetails.invoiceItem.product',
            'returnDetails.additionalItem',
            'returnDetails.customItem'
        ])->findOrFail($id);

        // Ensure totals are calculated using the revised logic
        $totals = $invoice->calculateTotals();

        return view('invoices.print', compact('invoice', 'totals'));
    }

    /**
     * Download the invoice as PDF.
     */
    public function download($id)
    {
        $invoice = Invoice::with([
            'invoiceItems.product',
            'additionalItems',
            'customItems',
            'returnDetails.invoiceItem.product',
            'returnDetails.additionalItem.product',
            'returnDetails.customItem'
        ])->findOrFail($id);

        $totals = $invoice->calculateTotals();

        $pdf = Pdf::loadView('invoices.download', compact('invoice', 'totals'));

        return $pdf->download("invoice-{$invoice->id}.pdf");
    }



    public function processReturns(Request $request, $invoiceId)
    {
        $isSeasonal = session('category') === 'season';

        $messages = [
            'returns.required' => 'You must provide at least one return item.',
            'returns.*.*.quantity.required_if' => 'The quantity is required when an item is selected.',
            'returns.*.*.quantity.integer' => 'The quantity must be a whole number.',
            'returns.*.*.quantity.min' => 'The quantity must be at least 1.',
            'returns.*.*.days_of_use.required_if' => 'The days of use is required when an item is selected.',
            'returns.*.*.days_of_use.integer' => 'The days of use must be a whole number.',
            'returns.*.*.return_date.required_if' => 'The return date is required when an item is selected.',
            'returns.*.*.return_date.date' => 'The return date must be a valid date.',
        ];

        $attributes = [
            'returns.*.*.selected' => 'item selection',
            'returns.*.*.quantity' => 'returned quantity',
            'returns.*.*.days_of_use' => 'days of use',
            'returns.*.*.return_date' => 'return date',
        ];

        $rules = [
            'returns' => 'required|array',
            'returns.*.*.selected' => 'sometimes|required|boolean',
            'returns.*.*.quantity' => 'required_if:returns.*.*.selected,1|integer|min:1',
            'returns.*.*.days_of_use' => $isSeasonal
                ? 'nullable|integer|min:1'
                : 'required_if:returns.*.*.selected,1|integer|min:1',
            'returns.*.*.return_date' => $isSeasonal ? 'nullable|date' : 'required_if:returns.*.*.selected,1|date',
        ];

        $validated = $request->validate($rules, $messages, $attributes);

        $invoice = Invoice::findOrFail($invoiceId);

        DB::beginTransaction();

        try {
            $totalRefund = 0;

            foreach ($validated['returns'] as $type => $items) {
                foreach ($items as $id => $item) {
                    if (!isset($item['selected']) || !$item['selected']) {
                        continue;
                    }

                    $model = match ($type) {
                        'original' => InvoiceItem::findOrFail($id),
                        'additional' => AdditionalItem::findOrFail($id),
                        'custom' => CustomItem::findOrFail($id),
                        default => throw new \Exception('Invalid return type.'),
                    };

                    $returnedQuantity = $item['quantity'];
                    $daysUsed = $isSeasonal ? 1 : ($item['days_of_use'] ?? 1);

                    if ($returnedQuantity > $model->quantity - $model->returned_quantity) {
                        throw new \Exception('Returned quantity exceeds available quantity.');
                    }

                    $refundAmount = 0;

                    // Calculate refund only for unused days
                    if (!$isSeasonal && $daysUsed < ($model->days ?? 1)) {
                        $unusedDays = ($model->days ?? 1) - $daysUsed;
                        $refundAmount = $unusedDays * $model->price * $returnedQuantity;
                    }

                    $totalRefund += $refundAmount;

                    ReturnDetail::create([
                        'invoice_id' => $invoice->id,
                        'invoice_item_id' => $type === 'original' ? $model->id : null,
                        'additional_item_id' => $type === 'additional' ? $model->id : null,
                        'custom_item_id' => $type === 'custom' ? $model->id : null,
                        'product_id' => $type === 'custom' ? null : $model->product_id, // Make product_id null for custom items
                        'returned_quantity' => $returnedQuantity,
                        'days_used' => $daysUsed,
                        'cost' => $refundAmount,
                        'return_date' => $isSeasonal ? now() : Carbon::parse($item['return_date']),
                    ]);

                    $model->returned_quantity += $returnedQuantity;
                    if ($model->returned_quantity >= $model->quantity) {
                        $model->status = 'returned';
                    }
                    $model->save();
                }
            }

            $allOriginalItemsReturned = $invoice->invoiceItems()->whereColumn('returned_quantity', '<', 'quantity')->doesntExist();
            $allAdditionalItemsReturned = $invoice->additionalItems()->whereColumn('returned_quantity', '<', 'quantity')->doesntExist();

            // Update invoice status
            $invoice->status = ($allOriginalItemsReturned && $allAdditionalItemsReturned) ? 'returned' : 'active';
            $invoice->save();

            DB::commit();

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', 'Returns processed successfully. Total refund: $' . number_format($totalRefund, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process returns: ' . $e->getMessage());
        }
    }


    public function addDates(Request $request, $invoiceId)
    {
        $messages = [
            'returns.required' => 'You must provide at least one item.',
            'returns.*.*.from.required_if' => 'The From Date is required when an item is selected.',
            'returns.*.*.from.date' => 'The From Date must be a valid date.',
            'returns.*.*.to.required_if' => 'The To Date is required when an item is selected.',
            'returns.*.*.to.date' => 'The To Date must be a valid date.',
            'returns.*.*.to.after_or_equal' => 'The To Date must be after or equal to the From Date.',
        ];

        $rules = [
            'returns' => 'required|array',
            'returns.*.*.selected' => 'sometimes|required|boolean',
            'returns.*.*.from' => 'required_if:returns.*.*.selected,1|date',
            'returns.*.*.to' => 'required_if:returns.*.*.selected,1|date|after_or_equal:returns.*.*.from',
        ];

        $validated = $request->validate($rules, $messages);

        $invoice = Invoice::findOrFail($invoiceId);

        DB::beginTransaction();

        try {
            foreach ($validated['returns'] as $type => $items) {
                foreach ($items as $id => $item) {
                    if (empty($item['selected'])) {
                        continue;
                    }

                    $model = match ($type) {
                        'original' => InvoiceItem::findOrFail($id),
                        'additional' => AdditionalItem::findOrFail($id),
                        'custom' => CustomItem::findOrFail($id),
                        default => throw new \Exception('Invalid item type.'),
                    };

                    $fromDate = Carbon::parse($item['from']);
                    $toDate = Carbon::parse($item['to']);

                    // Save the dates in the model (or a separate table if multiple ranges)
                    $model->rental_start_date = $fromDate;
                    $model->rental_end_date = $toDate;
                    $model->save();

                    // Optional: save to ReturnDetail table if you want a separate log
                    // ReturnDetail::create([
                    //     'invoice_id' => $invoice->id,
                    //     'invoice_item_id' => $type === 'original' ? $model->id : null,
                    //     'additional_item_id' => $type === 'additional' ? $model->id : null,
                    //     'custom_item_id' => $type === 'custom' ? $model->id : null,
                    //     'from_date' => $fromDate,
                    //     'to_date' => $toDate,
                    // ]);
                }
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', 'Dates added/updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add dates: ' . $e->getMessage());
        }
    }


    // public function processReturns(Request $request, $invoiceId)
    // {
    //     $isSeasonal = session('category') === 'season';

    //     $messages = [
    //         'returns.required' => 'You must provide at least one return item.',
    //         'returns.*.*.quantity.required_if' => 'The quantity is required when an item is selected.',
    //         'returns.*.*.quantity.integer' => 'The quantity must be a whole number.',
    //         'returns.*.*.quantity.min' => 'The quantity must be at least 1.',
    //         'returns.*.*.days_of_use.required_if' => 'The days of use is required when an item is selected.',
    //         'returns.*.*.days_of_use.integer' => 'The days of use must be a whole number.',
    //         'returns.*.*.return_date.required_if' => 'The return date is required when an item is selected.',
    //         'returns.*.*.return_date.date' => 'The return date must be a valid date.',
    //     ];

    //     $attributes = [
    //         'returns.*.*.selected' => 'item selection',
    //         'returns.*.*.quantity' => 'returned quantity',
    //         'returns.*.*.days_of_use' => 'days of use',
    //         'returns.*.*.return_date' => 'return date',
    //     ];

    //     $rules = [
    //         'returns' => 'required|array',
    //         'returns.*.*.selected' => 'sometimes|required|boolean',
    //         'returns.*.*.quantity' => 'required_if:returns.*.*.selected,1|integer|min:1',
    //         'returns.*.*.days_of_use' => $isSeasonal
    //             ? 'nullable|integer|min:1'
    //             : 'required_if:returns.*.*.selected,1|integer|min:1',
    //         'returns.*.*.return_date' => $isSeasonal ? 'nullable|date' : 'required_if:returns.*.*.selected,1|date',
    //     ];

    //     $validated = $request->validate($rules, $messages, $attributes);

    //     $invoice = Invoice::findOrFail($invoiceId);

    //     DB::beginTransaction();

    //     try {
    //         $totalRefund = 0;

    //         foreach ($validated['returns'] as $type => $items) {
    //             foreach ($items as $id => $item) {
    //                 if (!isset($item['selected']) || !$item['selected']) {
    //                     continue;
    //                 }

    //                 $model = ($type === 'original')
    //                     ? InvoiceItem::findOrFail($id)
    //                     : AdditionalItem::findOrFail($id);

    //                 $returnedQuantity = $item['quantity'];
    //                 $daysUsed = $isSeasonal ? 1 : ($item['days_of_use'] ?? 1);

    //                 if ($returnedQuantity > $model->quantity - $model->returned_quantity) {
    //                     throw new \Exception('Returned quantity exceeds available quantity.');
    //                 }

    //                 $refundAmount = 0;

    //                 // Calculate refund only for unused days
    //                 if (!$isSeasonal && $daysUsed < $model->days) {
    //                     $unusedDays = $model->days - $daysUsed;
    //                     $refundAmount = $unusedDays * $model->price * $returnedQuantity;
    //                 }

    //                 $totalRefund += $refundAmount;

    //                 ReturnDetail::create([
    //                     'invoice_id' => $invoice->id,
    //                     'invoice_item_id' => $type === 'original' ? $model->id : null,
    //                     'additional_item_id' => $type === 'additional' ? $model->id : null,
    //                     'product_id' => $model->product_id,
    //                     'returned_quantity' => $returnedQuantity,
    //                     'days_used' => $daysUsed,
    //                     'cost' => $refundAmount,
    //                     'return_date' => $isSeasonal ? now() : Carbon::parse($item['return_date']),
    //                 ]);

    //                 $model->returned_quantity += $returnedQuantity;
    //                 if ($model->returned_quantity >= $model->quantity) {
    //                     $model->status = 'returned';
    //                 }
    //                 $model->save();
    //             }
    //         }

    //         $allOriginalItemsReturned = $invoice->invoiceItems()->whereColumn('returned_quantity', '<', 'quantity')->doesntExist();
    //         $allAdditionalItemsReturned = $invoice->additionalItems()->whereColumn('returned_quantity', '<', 'quantity')->doesntExist();

    //         // Update invoice status
    //         $invoice->status = ($allOriginalItemsReturned && $allAdditionalItemsReturned) ? 'returned' : 'active';
    //         $invoice->save();

    //         DB::commit();

    //         return redirect()->route('invoices.show', $invoice->id)
    //             ->with('success', 'Returns processed successfully. Total refund: $' . number_format($totalRefund, 2));
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->with('error', 'Failed to process returns: ' . $e->getMessage());
    //     }
    // }

    public function addItems(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.days' => 'nullable|integer|min:1',
            'products.*.rental_start_date' => 'nullable|date',
            'products.*.rental_end_date' => 'nullable|date',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['products'] as $product) {
                $itemSubtotal = $product['price'] * $product['quantity'] * ($product['days'] ?? 1);

                AdditionalItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'returned_quantity' => 0,
                    'price' => $product['price'],
                    'days' => $product['days'] ?? null,
                    'total_price' => $itemSubtotal,
                    'rental_start_date' => $product['rental_start_date'] ?? null,
                    'rental_end_date' => $product['rental_end_date'] ?? null,
                    'status' => 'active',
                ]);
            }

            // Update paid amount if provided
            if (isset($validated['amount_paid'])) {
                $invoice->paid_amount += $validated['amount_paid'];
            }

            $invoice->save();

            DB::commit();

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', 'Items added successfully.');
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

        // Update the paid status and dynamically set the status field
        $invoice->update([
            'paid' => $validated['paid'],
            'status' => $validated['paid'] ? 'active' : 'draft',
        ]);

        return redirect()->route('invoices.edit', $id)->with('success', 'Payment status updated successfully.');
    }

    public function updateInvoiceStatus(Request $request, $id)
    {
        // Fetch the invoice
        $invoice = Invoice::findOrFail($id);

        // Validate the request
        $validated = $request->validate([
            'status' => 'required|in:returned,overdue', // Ensure status is valid enum
        ]);

        // If status is 'returned', trigger processReturns
        if ($validated['status'] === 'returned') {
            // Gather all items and additional items for processing
            $returns = [
                'original' => $invoice->invoiceItems->mapWithKeys(function ($item) {
                    return [$item->id => ['selected' => true, 'quantity' => $item->quantity]];
                })->toArray(),
                'additional' => $invoice->additionalItems->mapWithKeys(function ($item) {
                    return [$item->id => ['selected' => true, 'quantity' => $item->quantity]];
                })->toArray(),
            ];

            // Create a mock request with the returns data
            $mockRequest = new \Illuminate\Http\Request();
            $mockRequest->replace(['returns' => $returns]);

            // Call the processReturns method on the current controller instance
            $this->processReturns($mockRequest, $id);
        }

        // Update the invoice status
        $invoice->status = $validated['status'];
        $invoice->save();

        // Redirect back with success message
        return redirect()->route('invoices.edit', $id)->with('success', 'Invoice status updated and all items returned successfully.');
    }


    // public function updateInvoiceStatus(Request $request, $id)
    // {
    //     // Fetch the invoice
    //     $invoice = Invoice::findOrFail($id);

    //     // Validate the request
    //     $validated = $request->validate([
    //         'status' => 'required|in:returned,overdue', // Ensure status is valid enum
    //     ]);

    //     // Update the invoice status
    //     $invoice->status = $validated['status'];
    //     $invoice->save();

    //     // Update all related InvoiceItem statuses
    //     $invoice->invoiceItems()->update(['status' => 'returned']);

    //     // Update all related AdditionalItem statuses
    //     $invoice->additionalItems()->update(['status' => 'returned']);

    //     // Redirect back with success message
    //     return redirect()->route('invoices.edit', $id)->with('success', 'Invoice, items, and additional items status updated successfully.');
    // }



    public function paid(Request $request)
    {
        // Retrieve the selected category from the session, default to 'daily'
        $selectedCategory = $request->query('category', session('category', 'daily'));

        // Store the selected category in the session for persistence
        session(['category' => $selectedCategory]);

        // Build the query
        $invoices = Invoice::with('customer')
            ->whereHas('category', function ($query) use ($selectedCategory) {
                $query->where('name', $selectedCategory);
            })
            ->whereColumn('paid_amount', '>=', 'total_amount') // Unpaid invoices only
            ->get();

        // Pass the selected category to the view
        return view('invoices.paid', compact('invoices', 'selectedCategory'));
    }


    public function unpaid(Request $request)
    {
        // Retrieve the selected category from the session, default to 'daily'
        $selectedCategory = $request->query('category', session('category', 'daily'));

        // Store the selected category in the session for persistence
        session(['category' => $selectedCategory]);

        // Build the query
        $invoices = Invoice::with('customer')
            ->whereHas('category', function ($query) use ($selectedCategory) {
                $query->where('name', $selectedCategory);
            })
            ->whereColumn('paid_amount', '<', 'total_amount') // Unpaid invoices only
            ->get();

        // Pass the selected category to the view
        return view('invoices.unpaid', compact('invoices', 'selectedCategory'));
    }


    public function addPayment(Request $request, $invoiceId)
    {
        // Find the invoice
        $invoice = Invoice::findOrFail($invoiceId);

        // Get the calculated totals
        $totals = $invoice->calculateTotals();

        // Use the balance due from the calculated totals
        $balanceDue = $totals['balanceDue'];

        // Validate the new payment amount and payment method
        $validated = $request->validate([
            'new_payment' => 'required|numeric|min:0|max:' . $balanceDue,
            'payment_method' => 'required|in:cash,credit_card',
        ]);

        // Add the new payment to the invoice_payments table
        $payment = new InvoicePayment();
        $payment->invoice_id = $invoice->id;
        $payment->amount = $validated['new_payment'];
        $payment->payment_method = $validated['payment_method'];
        $payment->payment_date = now(); // Current date and time for the payment
        $payment->save();

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Payment added successfully.');
    }

    // public function addPayment(Request $request, $invoiceId)
    // {
    //     // Find the invoice
    //     $invoice = Invoice::findOrFail($invoiceId);

    //     // Get the calculated totals
    //     $totals = $invoice->calculateTotals();

    //     // Use the balance due from the calculated totals
    //     $balanceDue = $totals['balanceDue'];

    //     // Validate the new payment amount
    //     $validated = $request->validate([
    //         'new_payment' => 'required|numeric|min:0|max:' . $balanceDue,
    //     ]);

    //     // Update the paid amount
    //     $invoice->paid_amount += $validated['new_payment'];

    //     // Save the invoice
    //     $invoice->save();

    //     return redirect()->route('invoices.show', $invoice->id)
    //         ->with('success', 'Payment added successfully.');
    // }

    public function updateNote(Request $request, Invoice $invoice)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $invoice->note = $request->note;
        $invoice->save();

        return redirect()->back()->with('success', 'Note updated successfully.');
    }

    public function showRemoveItems($invoiceId)
    {
        $invoice = Invoice::with(['invoiceItems.product', 'customItems', 'additionalItems.product'])->findOrFail($invoiceId);
        return view('invoices.remove-items', compact('invoice'));
    }

    public function destroyItem($id)
    {
        $item = InvoiceItem::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Item removed successfully.');
    }

    public function destroyCustom($id)
    {
        $customItem = CustomItem::findOrFail($id);
        $customItem->delete();
        return redirect()->back()->with('success', 'Custom item deleted.');
    }

    public function destroyAdditional($id)
    {
        $additionalItem = AdditionalItem::findOrFail($id);
        $additionalItem->delete();
        return redirect()->back()->with('success', 'Additional item deleted.');
    }
}
