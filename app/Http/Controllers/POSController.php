<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Traits\FileUploadTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    use FileUploadTrait;

    // Display POS Interface
    public function index()
    {
        $products = Product::whereHas('category', function ($query) {
            $query->where('name', session('category', 'daily'));
        })->get();

        $customers = Customer::all();

        return view('pos.index', compact('products', 'customers'));
    }

    public function checkout(Request $request)
    {
        $categoryName = session('category', 'daily');

        $rules = [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255|required_without:customer_id',
            'customer_phone' => 'nullable|string|max:255|required_without:customer_id',
            'customer_address' => 'nullable|string|max:255|required_without:customer_id',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'nullable|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
            'cart.*.name' => 'nullable|string|max:255',
            'total_discount' => 'nullable|numeric|min:0|max:100',
            'deposit' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,credit_card',
            'note' => 'nullable|string|max:255',
        ];

        if ($categoryName === 'daily') {
            $rules['rental_start_date'] = 'required|date';
            $rules['rental_end_date'] = 'required|date|after_or_equal:rental_start_date';
            $rules['rental_days'] = 'required|integer|min:1';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $customer = $request->filled('customer_id')
                ? Customer::findOrFail($request->customer_id)
                : Customer::create([
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'address' => $request->customer_address,
                ]);

            $category = Category::where('name', $categoryName)->firstOrFail();

            $subtotal = 0;
            $invoiceItems = [];
            $customItems = [];

            foreach ($request->cart as $cartItem) {
                $quantity = $cartItem['quantity'];
                $price = $cartItem['price'];
                $totalPrice = ($categoryName === 'daily')
                    ? $quantity * $price * $request->rental_days
                    : $quantity * $price;

                if (!empty($cartItem['id'])) {
                    $product = Product::findOrFail($cartItem['id']);
                    $invoiceItems[] = new InvoiceItem([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total_price' => $totalPrice,
                        'rental_start_date' => $categoryName === 'daily' ? $request->rental_start_date : null,
                        'rental_end_date' => $categoryName === 'daily' ? $request->rental_end_date : null,
                        'days' => $categoryName === 'daily' ? $request->rental_days : null,
                        'returned_quantity' => 0,
                        'added_quantity' => 0,
                    ]);
                } else {
                    $customItems[] = new CustomItem([
                        'name' => $cartItem['name'],
                        'description' => $cartItem['description'] ?? '',
                        'price' => $price,
                        'quantity' => $quantity,
                    ]);
                }

                $subtotal += $totalPrice;
            }

            $totalDiscount = $request->total_discount ?? 0;
            $discountAmount = ($subtotal * $totalDiscount) / 100;
            $totalAmount = $subtotal - $discountAmount;
            $deposit = $request->deposit ?? 0;
            $paymentAmount = $request->payment_amount ?? 0;

            if ($deposit + $paymentAmount > $totalAmount) {
                return response()->json(['error' => 'Payment and deposit exceed total amount.'], 400);
            }

            $invoiceData = [
                'customer_id' => $customer->id,
                'user_id' => auth()->user()->id,
                'category_id' => $category->id,
                'total_discount' => $totalDiscount,
                'deposit' => $deposit,
                'total_amount' => $totalAmount,
                'paid_amount' => $paymentAmount,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
            ];

            if ($categoryName === 'daily') {
                $invoiceData['rental_start_date'] = $request->rental_start_date;
                $invoiceData['rental_end_date'] = $request->rental_end_date;
                $invoiceData['days'] = $request->rental_days;
            }

            $invoice = Invoice::create($invoiceData);

            if (!empty($invoiceItems)) {
                $invoice->items()->saveMany($invoiceItems);
            }

            if (!empty($customItems)) {
                foreach ($customItems as $item) {
                    $item->invoice_id = $invoice->id;
                    $item->save();
                }
            }

            DB::commit();

            return response()->json(['invoice_id' => $invoice->id, 'message' => 'Checkout successful.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Exception:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['error' => 'An error occurred while processing checkout.'], 500);
        }
    }



    // public function checkout(Request $request)
    // {
    //     $categoryName = session('category', 'daily');

    //     // Validation rules
    //     $rules = [
    //         'customer_id' => 'nullable|exists:customers,id',
    //         'customer_name' => 'nullable|string|max:255|required_without:customer_id',
    //         'customer_phone' => 'nullable|string|max:255|required_without:customer_id',
    //         'customer_address' => 'nullable|string|max:255|required_without:customer_id',
    //         'cart' => 'required|array|min:1',
    //         'cart.*.id' => 'required|exists:products,id',
    //         'cart.*.quantity' => 'required|integer|min:1',
    //         'total_discount' => 'nullable|numeric|min:0|max:100',
    //         'deposit' => 'nullable|numeric|min:0',
    //         'payment_amount' => 'nullable|numeric|min:0',
    //         'payment_method' => 'required|in:cash,credit_card',
    //         'note' => 'nullable',
    //     ];

    //     if ($categoryName === 'daily') {
    //         $rules['rental_start_date'] = 'required|date';
    //         $rules['rental_end_date'] = 'required|date|after_or_equal:rental_start_date';
    //         $rules['rental_days'] = 'required|integer|min:1';
    //     }

    //     $request->validate($rules);

    //     try {
    //         DB::beginTransaction();

    //         // Handle Customer
    //         if ($request->filled('customer_id')) {
    //             $customer = Customer::findOrFail($request->customer_id);
    //         } else {
    //             $customer = Customer::create([
    //                 'name' => $request->customer_name,
    //                 'phone' => $request->customer_phone,
    //                 'address' => $request->customer_address,
    //             ]);
    //         }

    //         // Retrieve the selected category
    //         $category = Category::where('name', $categoryName)->firstOrFail();

    //         // Calculate totals
    //         $subtotal = 0;
    //         $invoiceItems = [];
    //         foreach ($request->cart as $cartItem) {
    //             $product = Product::findOrFail($cartItem['id']);
    //             $quantity = $cartItem['quantity'];
    //             $price = $product->price;

    //             $totalPrice = ($categoryName === 'daily')
    //                 ? $quantity * $price * $request->rental_days
    //                 : $quantity * $price;

    //             $invoiceItems[] = new InvoiceItem([
    //                 'product_id' => $product->id,
    //                 'quantity' => $quantity,
    //                 'price' => $price,
    //                 'total_price' => $totalPrice,
    //                 'rental_start_date' => $categoryName === 'daily' ? $request->rental_start_date : null,
    //                 'rental_end_date' => $categoryName === 'daily' ? $request->rental_end_date : null,
    //                 'days' => $categoryName === 'daily' ? $request->rental_days : null,
    //                 'returned_quantity' => 0,
    //                 'added_quantity' => 0,
    //             ]);

    //             $subtotal += $totalPrice;
    //         }

    //         // Discount calculations
    //         $totalDiscount = $request->total_discount ?? 0;
    //         $discountAmount = ($subtotal * $totalDiscount) / 100;

    //         // Calculate the full total amount (before deposit)
    //         $totalAmount = $subtotal - $discountAmount;

    //         // Track deposit and payment
    //         $deposit = $request->deposit ?? 0;
    //         $paymentAmount = $request->payment_amount ?? 0;

    //         // Ensure payment and deposit do not exceed the total
    //         if ($deposit + $paymentAmount > $totalAmount) {
    //             return response()->json(['error' => 'Payment and deposit exceed total amount.'], 400);
    //         }

    //         // Paid amount includes only additional payments beyond the deposit
    //         $paidAmount = $paymentAmount;

    //         // Create the Invoice
    //         $invoiceData = [
    //             'customer_id' => $customer->id,
    //             'user_id' => auth()->user()->id,
    //             'category_id' => $category->id,
    //             'total_discount' => $totalDiscount,
    //             'deposit' => $deposit,
    //             'total_amount' => $totalAmount, // Full amount before subtracting deposit
    //             'paid_amount' => $paidAmount, // Additional payments beyond deposit
    //             'payment_method' => $request->payment_method,
    //             'note' => $request->note,
    //         ];

    //         if ($categoryName === 'daily') {
    //             $invoiceData['rental_start_date'] = $request->rental_start_date;
    //             $invoiceData['rental_end_date'] = $request->rental_end_date;
    //             $invoiceData['days'] = $request->rental_days;
    //         }

    //         $invoice = Invoice::create($invoiceData);

    //         // Attach items to the invoice
    //         $invoice->items()->saveMany($invoiceItems);

    //         DB::commit();

    //         return response()->json(['invoice_id' => $invoice->id, 'message' => 'Checkout successful.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Checkout Exception:', [
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //             'file' => $e->getFile(),
    //         ]);

    //         return response()->json(['error' => 'An error occurred while processing checkout.'], 500);
    //     }
    // }


    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|string|min:3',
            'phone' => 'required|numeric|unique:customers,phone,phone2',
            'phone2' => 'nullable|numeric|unique:customers,phone,phone2',
            'address' => 'nullable|string',
            'deposit_card' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // Optional file validation
        ]);

        // Handle file upload if provided
        $filePath = null;
        if ($request->hasFile('deposit_card')) {
            $filePath = $this->uploadImage($request, 'deposit_card', null, '/uploads/customers');
        }

        // Create new customer
        $customer = new Customer();
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->phone2 = $request->phone2;
        $customer->address = $request->address;
        $customer->deposit_card = $filePath;
        $customer->save();

        // Redirect back to POS page with the new customer pre-selected
        return redirect()->route('pos.index')->with('new_customer_id', $customer->id);
    }
}
