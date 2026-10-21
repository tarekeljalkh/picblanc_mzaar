<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Services\InvoicePaymentStatusQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // The app can switch databases by year, so never share dashboard
        // metrics between two year's databases.
        $databaseKey = preg_replace('/[^A-Za-z0-9_.-]/', '_', DB::connection()->getDatabaseName()) ?: 'default';

        // Customers are category-independent, while the remaining dashboard
        // values are category-specific. Keep the cache short-lived because
        // payments and returns can change these numbers during the day.
        $customersCount = Cache::remember(
            "dashboard.customers.count.{$databaseKey}",
            now()->addSeconds(10),
            fn () => Customer::count()
        );

        $dashboardMetrics = Cache::remember(
            "dashboard.metrics.{$databaseKey}.{$category->id}",
            now()->addSeconds(5),
            function () use ($category): array {
                $invoicesCount = Invoice::where('category_id', $category->id)->count();
                $statusCounts = app(InvoicePaymentStatusQuery::class)->counts($category->id, $category->name);

                if ($statusCounts !== null) {
                    $totalPaid = $statusCounts['fully_paid'];
                    $totalPartiallyPaid = $statusCounts['partially_paid'];
                    $totalUnpaid = $statusCounts['unpaid'];
                    $overdueCount = $statusCounts['overdue'];
                } else {
                    $totalPaid = 0;
                    $totalPartiallyPaid = 0;
                    $totalUnpaid = 0;
                    $overdueCount = 0;
                    $now = now();

                    Invoice::query()
                        ->where('category_id', $category->id)
                        ->select(['id', 'category_id', 'total_discount', 'deposit', 'rental_end_date'])
                        ->with([
                            'category:id,name',
                            'invoiceItems:id,invoice_id,price,quantity,rental_start_date,rental_end_date,days',
                            'customItems:id,invoice_id,price,quantity,rental_start_date,rental_end_date,days',
                            'additionalItems:id,invoice_id,price,quantity,rental_start_date,rental_end_date,days',
                            'payments:id,invoice_id,amount',
                            'returnDetails:id,invoice_id,invoice_item_id,additional_item_id,custom_item_id,days_used',
                            'returnDetails.invoiceItem:id,price,rental_start_date,rental_end_date,days',
                            'returnDetails.additionalItem:id,price,rental_start_date,rental_end_date,days',
                            'returnDetails.customItem:id,price,rental_start_date,rental_end_date,days',
                        ])
                        ->chunkById(500, function ($invoices) use (
                            &$totalPaid,
                            &$totalPartiallyPaid,
                            &$totalUnpaid,
                            &$overdueCount,
                            $now
                        ) {
                            foreach ($invoices as $invoice) {
                                $paymentStatus = $invoice->payment_status;

                                if ($paymentStatus === 'fully_paid') {
                                    $totalPaid++;
                                } elseif ($paymentStatus === 'partially_paid') {
                                    $totalPartiallyPaid++;
                                } elseif ($paymentStatus === 'unpaid') {
                                    $totalUnpaid++;
                                }

                                if ($invoice->rental_end_date < $now && $paymentStatus !== 'fully_paid') {
                                    $overdueCount++;
                                }
                            }
                        });
                }

                // An invoice is not returned when any active item still has
                // quantity outstanding. Build that ID set once; returned and
                // not-returned invoices are complements of each other.
                $unreturnedInvoiceIds = DB::table('invoice_items')
                    ->select('invoice_id')
                    ->whereNull('deleted_at')
                    ->whereColumn('quantity', '>', 'returned_quantity')
                    ->unionAll(
                        DB::table('additional_items')
                            ->select('invoice_id')
                            ->whereColumn('quantity', '>', 'returned_quantity')
                    )
                    ->unionAll(
                        DB::table('custom_items')
                            ->select('invoice_id')
                            ->whereColumn('quantity', '>', 'returned_quantity')
                    );

                $notReturnedCount = Invoice::where('category_id', $category->id)
                    ->whereIn('id', $unreturnedInvoiceIds)
                    ->count();
                $returnedCount = $invoicesCount - $notReturnedCount;

                $totalRevenue = InvoicePayment::whereHas(
                    'invoice',
                    fn($query) => $query->where('category_id', $category->id)
                )->sum('amount');

                return compact(
                    'invoicesCount',
                    'totalPaid',
                    'totalPartiallyPaid',
                    'totalUnpaid',
                    'notReturnedCount',
                    'returnedCount',
                    'overdueCount',
                    'totalRevenue'
                );
            }
        );

        extract($dashboardMetrics, EXTR_OVERWRITE);

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
            'categoryName'
        ));
    }


    public function trialBalance(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $selectedCategory = session('category', 'daily');

        $dateFilters = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);
        $fromDate = $dateFilters['from_date'] ?? Carbon::today()->toDateString();
        $toDate = $dateFilters['to_date'] ?? Carbon::today()->toDateString();

        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();
        $category = Category::where('name', $selectedCategory)->firstOrFail();

        // 🧾 Sum all payments grouped by method (using payment_date)
        $paymentSums = InvoicePayment::whereBetween('payment_date', [$from, $to])
            ->whereHas('invoice', function ($query) use ($category, $isAdmin, $user) {
                $query->where('category_id', $category->id);
                if (!$isAdmin) {
                    $query->where('user_id', $user->id);
                }
            })
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $totalPaidByCash = $paymentSums['cash'] ?? 0;
        $totalPaidByCreditCard = $paymentSums['credit_card'] ?? 0;

        // 🧮 Sum unpaid balances in SQL, applying the same date-range rules as
        // the original trial-balance calculation without loading every invoice.
        $invoiceDateFilter = function ($query) use ($selectedCategory, $from, $to): void {
            if ($selectedCategory === 'season') {
                $query->whereBetween('created_at', [$from, $to]);
                return;
            }

            $query->where(function ($dateQuery) use ($from, $to): void {
                $dateQuery->whereBetween('rental_start_date', [$from, $to])
                    ->orWhereBetween('rental_end_date', [$from, $to])
                    ->orWhere(function ($overlappingQuery) use ($from, $to): void {
                        $overlappingQuery
                            ->where('rental_start_date', '<=', $from)
                            ->where('rental_end_date', '>=', $to);
                    });
            });
        };

        $totalUnpaidInvoices = app(InvoicePaymentStatusQuery::class)->unpaidBalanceSum(
            $category->id,
            $selectedCategory,
            $isAdmin ? null : $user->id,
            $invoiceDateFilter
        );

        // Keep a chunked fallback for non-MySQL installations.
        if ($totalUnpaidInvoices === null) {
            $totalUnpaidInvoices = 0;
            $invoiceQuery = Invoice::with([
                'payments',
                'category',
                'invoiceItems',
                'customItems',
                'additionalItems',
                'returnDetails.invoiceItem',
                'returnDetails.additionalItem',
                'returnDetails.customItem',
            ])
                ->where('category_id', $category->id)
                ->when(!$isAdmin, fn ($query) => $query->where('user_id', $user->id));

            $invoiceDateFilter($invoiceQuery);
            $invoiceQuery->chunkById(500, function ($invoices) use (&$totalUnpaidInvoices): void {
                foreach ($invoices as $invoice) {
                    $totalUnpaidInvoices += max(0, $invoice->calculateTotals()['balanceDue'] ?? 0);
                }
            });
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
        $dateFilters = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);
        $fromDate = $dateFilters['from_date'] ?? Carbon::today()->toDateString();
        $toDate = $dateFilters['to_date'] ?? Carbon::today()->toDateString();

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
            ->when($user->role !== 'admin', fn($query) => $query->where('user_id', $user->id))
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
