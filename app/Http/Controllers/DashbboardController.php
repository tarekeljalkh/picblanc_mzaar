<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashbboardController extends Controller
{
    public function index()
    {
        // Retrieve the selected category from the session, default to 'daily'
        $categoryName = session('category', 'daily');

        // Fetch the category based on the name
        $category = Category::where('name', $categoryName)->first();

        if (!$category) {
            abort(404, 'Category not found.');
        }

        // Fetch total counts
        $customersCount = Customer::count();
        $invoicesCount = Invoice::where('category_id', $category->id)->count();

        // ✅ Load invoices with payments
        // $invoices = Invoice::where('category_id', $category->id)->with('payments')->get();
        $invoices = Invoice::where('category_id', $category->id)
            ->with([
                'customer',
                'category',
                'invoiceItems',
                'customItems.invoice',          // in case you reference customItem->invoice
                'additionalItems.invoice',      // same here
                'payments',
                'returnDetails.invoiceItem',
                'returnDetails.additionalItem',
                'returnDetails.customItem',
            ])
            ->where('category_id', $category->id)
            ->get();

        // ✅ Use payment_status accessor for accurate classification
        $totalPaid = $invoices->filter(fn($invoice) => $invoice->payment_status === 'fully_paid')->count();
        $totalPartiallyPaid = $invoices->filter(fn($invoice) => $invoice->payment_status === 'partially_paid')->count();
        $totalUnpaid = $invoices->filter(fn($invoice) => $invoice->payment_status === 'unpaid')->count();

        // Not Returned
        $notReturnedCount = Invoice::where('category_id', $category->id)
            ->where(function ($query) {
                $query->whereHas('invoiceItems', fn($q) => $q->whereColumn('quantity', '>', 'returned_quantity'))
                    ->orWhereHas('additionalItems', fn($q) => $q->whereColumn('quantity', '>', 'returned_quantity'))
                    ->orWhereHas('customItems', fn($q) => $q->whereColumn('quantity', '>', 'returned_quantity'));
            })
            ->distinct()
            ->count();

        // Returned
        $returnedCount = Invoice::where('category_id', $category->id)
            ->whereDoesntHave('invoiceItems', fn($query) => $query->whereColumn('quantity', '>', 'returned_quantity'))
            ->whereDoesntHave('additionalItems', fn($query) => $query->whereColumn('quantity', '>', 'returned_quantity'))
            ->whereDoesntHave('customItems', fn($query) => $query->whereColumn('quantity', '>', 'returned_quantity'))
            ->count();

        // Overdue Count
        $overdueCount = $invoices->filter(function ($invoice) {
            $totals = $invoice->calculateTotals();
            $paid = round($invoice->payments->sum('amount'), 2);
            $due = round($totals['finalTotal'] ?? 0, 2);
            return $invoice->rental_end_date < now() && ($due - $paid) > 1.00;
        })->count();

        // Total revenue
        $totalRevenue = InvoicePayment::whereHas('invoice', fn($query) => $query->where('category_id', $category->id))->sum('amount');

        // Overdue revenue
        $overdueRevenue = $invoices->sum(function ($invoice) {
            $totals = $invoice->calculateTotals();
            $paid = round($invoice->payments->sum('amount'), 2);
            $due = round($totals['finalTotal'] ?? 0, 2);
            return max(0, $due - $paid);
        });

        // Paginate latest invoices
        $invoices = Invoice::with('customer')
            ->where('category_id', $category->id)
            ->where('status', '!=', 'returned')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard', compact(
            'customersCount',
            'invoicesCount',
            'totalPaid',
            'totalPartiallyPaid',
            'totalUnpaid',
            'notReturnedCount',
            'returnedCount',
            'overdueCount',
            'totalRevenue',
            'overdueRevenue',
            'invoices',
            'categoryName'
        ));
    }


    public function trialBalance(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $selectedCategory = session('category', 'daily');

        $fromDate = $request->input('from_date', Carbon::today()->toDateString());
        $toDate = $request->input('to_date', Carbon::today()->toDateString());

        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();

        // 🧾 Sum all payments grouped by method (using payment_date)
        $paymentSums = InvoicePayment::whereBetween('payment_date', [$from, $to])
            ->whereHas('invoice', function ($query) use ($selectedCategory, $isAdmin, $user) {
                $query->whereHas('category', fn($q) => $q->where('name', $selectedCategory));
                if (!$isAdmin) {
                    $query->where('user_id', $user->id);
                }
            })
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $totalPaidByCash = $paymentSums['cash'] ?? 0;
        $totalPaidByCreditCard = $paymentSums['credit_card'] ?? 0;

        // 🧮 Unpaid balances
        $invoices = Invoice::with(['payments', 'category', 'invoiceItems', 'customItems', 'additionalItems', 'returnDetails'])
            ->whereHas('category', fn($q) => $q->where('name', $selectedCategory))
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
            ->get();

        $totalUnpaidInvoices = 0;

        foreach ($invoices as $invoice) {
            $totals = $invoice->calculateTotals();
            $balanceDue = $totals['balanceDue'] ?? 0;

            if ($balanceDue > 0) {
                $totalUnpaidInvoices += $balanceDue;
            }
        }

        // ✅ Final data
        $trialBalanceData = [
            ['description' => 'Total Paid Invoices (Cash)', 'amount' => round($totalPaidByCash, 2)],
            ['description' => 'Total Paid by Credit Card', 'amount' => round($totalPaidByCreditCard, 2)],
            ['description' => 'Total Unpaid Invoices', 'amount' => round($totalUnpaidInvoices, 2)],
        ];

        return view('trial-balance.index', compact('trialBalanceData', 'fromDate', 'toDate', 'selectedCategory'));
    }

    // public function trialBalance(Request $request)
    // {
    //     $user = auth()->user();
    //     $isAdmin = $user->role === 'admin';
    //     $selectedCategory = session('category', 'daily');

    //     $fromDate = $request->input('from_date', Carbon::today()->toDateString());
    //     $toDate = $request->input('to_date', Carbon::today()->toDateString());

    //     $from = Carbon::parse($fromDate)->startOfDay();
    //     $to = Carbon::parse($toDate)->endOfDay();

    //     $totalPaidByCash = 0;
    //     $totalPaidByCreditCard = 0;
    //     $totalUnpaidInvoices = 0;

    //     // 🧾 Get payments based on actual payment_date
    //     $invoicePayments = InvoicePayment::whereBetween('payment_date', [$from, $to])
    //         ->whereHas('invoice', function ($query) use ($selectedCategory, $isAdmin, $user) {
    //             $query->whereHas('category', fn($q) => $q->where('name', $selectedCategory));
    //             if (!$isAdmin) {
    //                 $query->where('user_id', $user->id);
    //             }
    //         })
    //         ->get();

    //     // 💳 Sum payments by method
    //     foreach ($invoicePayments as $payment) {
    //         if ($payment->payment_method === 'cash') {
    //             $totalPaidByCash += $payment->amount;
    //         } elseif ($payment->payment_method === 'credit_card') {
    //             $totalPaidByCreditCard += $payment->amount;
    //         }
    //     }

    //     // 🧮 Calculate unpaid balances (based on invoice date ranges as before)
    //     $invoices = Invoice::with([
    //         'payments',
    //         'category',
    //         'invoiceItems',
    //         'customItems',
    //         'additionalItems',
    //         'returnDetails.invoiceItem',
    //         'returnDetails.additionalItem',
    //         'returnDetails.customItem',
    //     ])
    //         ->whereHas('category', fn($q) => $q->where('name', $selectedCategory))
    //         ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
    //         ->when($selectedCategory === 'season', function ($query) use ($from, $to) {
    //             $query->whereBetween('created_at', [$from, $to]);
    //         }, function ($query) use ($from, $to) {
    //             $query->where(function ($q) use ($from, $to) {
    //                 $q->whereBetween('rental_start_date', [$from, $to])
    //                   ->orWhereBetween('rental_end_date', [$from, $to])
    //                   ->orWhere(function ($q2) use ($from, $to) {
    //                       $q2->where('rental_start_date', '<=', $from)
    //                          ->where('rental_end_date', '>=', $to);
    //                   });
    //             });
    //         })
    //         ->get();

    //     foreach ($invoices as $invoice) {
    //         $totals = $invoice->calculateTotals();
    //         $balanceDue = $totals['balanceDue'] ?? 0;

    //         $totalUnpaidInvoices += $balanceDue;
    //     }

    //     // ✅ Final trial balance data
    //     $trialBalanceData = [
    //         ['description' => 'Total Paid Invoices (Cash)', 'amount' => round($totalPaidByCash, 2)],
    //         ['description' => 'Total Paid by Credit Card', 'amount' => round($totalPaidByCreditCard, 2)],
    //         ['description' => 'Total Unpaid Invoices', 'amount' => round($totalUnpaidInvoices, 2)],
    //     ];

    //     return view('trial-balance.index', compact('trialBalanceData', 'fromDate', 'toDate', 'selectedCategory'));
    // }


    public function trialBalanceByProducts(Request $request)
    {
        $user = auth()->user(); // Get the authenticated user

        // Set default "from" and "to" dates to today if not provided
        $fromDate = $request->input('from_date', Carbon::today()->toDateString());
        $toDate = $request->input('to_date', Carbon::today()->toDateString());

        // Parse the dates using Carbon
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();

        // Fetch the selected category from the session
        $categoryName = session('category', 'daily');
        $category = Category::where('name', $categoryName)->first();

        if (!$category) {
            return redirect()->back()->withErrors('Invalid category selected.');
        }

        // Check if the category is "season"
        $isSeasonal = $category->name === 'season';

        // Fetch all products filtered by rental dates only (ignore payments)
        $products = Product::with([
            'invoiceItems' => function ($query) use ($from, $to, $category, $isSeasonal, $user) {
                $query->whereHas('invoice', function ($invoiceQuery) use ($category, $from, $to, $isSeasonal, $user) {
                    $invoiceQuery->where('category_id', $category->id);

                    if ($user->role !== 'admin') {
                        $invoiceQuery->where('user_id', $user->id);
                    }

                    if ($isSeasonal) {
                        $invoiceQuery->whereBetween('created_at', [$from, $to]);
                    } else {
                        $invoiceQuery->where(function ($dateQuery) use ($from, $to) {
                            $dateQuery->whereBetween('rental_start_date', [$from, $to])
                                ->orWhereBetween('rental_end_date', [$from, $to])
                                ->orWhere(function ($spanQuery) use ($from, $to) {
                                    $spanQuery->where('rental_start_date', '<=', $to)
                                        ->where('rental_end_date', '>=', $from);
                                });
                        });
                    }
                });
            },
            'additionalItems' => function ($query) use ($from, $to, $category, $isSeasonal, $user) {
                $query->whereHas('invoice', function ($invoiceQuery) use ($category, $from, $to, $isSeasonal, $user) {
                    $invoiceQuery->where('category_id', $category->id);

                    if ($user->role !== 'admin') {
                        $invoiceQuery->where('user_id', $user->id);
                    }

                    if ($isSeasonal) {
                        $invoiceQuery->whereBetween('created_at', [$from, $to]);
                    } else {
                        $invoiceQuery->where(function ($dateQuery) use ($from, $to) {
                            $dateQuery->whereBetween('rental_start_date', [$from, $to])
                                ->orWhereBetween('rental_end_date', [$from, $to])
                                ->orWhere(function ($spanQuery) use ($from, $to) {
                                    $spanQuery->where('rental_start_date', '<=', $to)
                                        ->where('rental_end_date', '>=', $from);
                                });
                        });
                    }
                });
            }
        ])->get();

        // Fetch invoices that contain custom items within the date range
        $invoices = Invoice::where('category_id', $category->id)
            ->where(function ($query) use ($from, $to, $isSeasonal) {
                if ($isSeasonal) {
                    $query->whereBetween('created_at', [$from, $to]);
                } else {
                    $query->whereBetween('rental_start_date', [$from, $to])
                        ->orWhereBetween('rental_end_date', [$from, $to])
                        ->orWhere(function ($q) use ($from, $to) {
                            $q->where('rental_start_date', '<=', $to)
                                ->where('rental_end_date', '>=', $from);
                        });
                }
            })
            ->with(['customItems']) // Fetch custom items directly from invoices
            ->get();

        $productBalances = [];

        // Process invoice and additional items
        foreach ($products as $product) {
            $totalQuantity = 0;

            // Calculate totals for original invoice items
            foreach ($product->invoiceItems as $item) {
                $remainingQuantity = $item->quantity - ($item->returned_quantity ?? 0);
                $totalQuantity += $remainingQuantity;
            }

            // Calculate totals for additional items
            foreach ($product->additionalItems as $item) {
                $remainingQuantity = $item->quantity - ($item->returned_quantity ?? 0);
                $totalQuantity += $remainingQuantity;
            }

            // Add to productBalances only if there's a positive quantity
            if ($totalQuantity > 0) {
                $productBalances[] = [
                    'product' => $product->name,
                    'quantity' => $totalQuantity,
                ];
            }
        }

        // Process custom items
        foreach ($invoices as $invoice) {
            foreach ($invoice->customItems as $customItem) {
                $remainingQuantity = $customItem->quantity - ($customItem->returned_quantity ?? 0);

                if ($remainingQuantity > 0) {
                    $productBalances[] = [
                        'product' => $customItem->name, // Custom item name
                        'quantity' => $remainingQuantity,
                    ];
                }
            }
        }

        // Return the view with only the quantities of rented products
        return view('trial-balance.products', compact('productBalances', 'fromDate', 'toDate'));
    }
}
