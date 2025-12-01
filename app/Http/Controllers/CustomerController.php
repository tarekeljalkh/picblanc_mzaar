<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Models\Customer;
use App\Models\Invoice;
use App\Traits\FileUploadTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(CustomerDataTable $dataTable)
    {
        //return $dataTable->render('customers.index');
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    public function getCustomer($id)
    {
        $customer = Customer::find($id);

        // Return customer data as JSON
        return response()->json($customer);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|string|min:3',
            'phone' => 'required|numeric|unique:customers,phone,phone2',
            'phone2' => 'nullable|numeric|unique:customers,phone,phone2',
            'address' => 'required|string',
            'deposit_card' => 'required|file|mimes:jpeg,png,jpg,gif', // Validate image upload
        ]);

        // Handle file upload (if provided)
        $filePath = $this->uploadImage($request, 'deposit_card', null, '/uploads/customers');

        // Create a new customer
        $customer = new Customer();
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->phone2 = $request->phone2;
        $customer->address = $request->address;
        $customer->deposit_card = $filePath; // Save the file path if uploaded
        $customer->save();

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $customer = Customer::find($id);
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|min:3',
            'phone' => [
                'required',
                'numeric',
                'unique:customers,phone,' . $id,       // Exclude current customer ID for phone
                'unique:customers,phone2,' . $id       // Ensure phone is not in phone2 of other customers
            ],
            'phone2' => [
                'nullable',
                'numeric',
                'unique:customers,phone,' . $id,       // Ensure phone2 is not in phone column of other customers
                'unique:customers,phone2,' . $id,      // Exclude current customer ID for phone2
                'different:phone'                      // Ensure phone2 is different from phone
            ],
                    'address' => 'required|string',
            'deposit_card' => 'nullable|image|max:2048', // Optional image file upload
        ]);

        $customer = Customer::findOrFail($id);

        // Handle image file upload, replace old file if a new one is uploaded
        $filePath = $this->uploadImage($request, 'deposit_card', $customer->file, '/uploads/customers');

        // Update customer details
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->phone2 = $request->phone2;
        $customer->address = $request->address;
        $customer->deposit_card = $filePath ?? $customer->deposit_card; // Keep old file if no new one uploaded
        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // Check if the customer has a deposit card before trying to remove the image
            if (!empty($customer->deposit_card)) {
                $this->removeImage($customer->deposit_card);
            }

            // Delete the customer
            $customer->delete();


            return response()->json(['status' => 'success', 'message' => 'Deleted Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong!']);
        }
    }

    public function rentalDetails(Request $request, $id)
    {
        // Find the customer by ID
        $customer = Customer::findOrFail($id);

        // Start building the invoices query
        $invoicesQuery = Invoice::where('customer_id', $id)
            ->with([
                'invoiceItems.product',  // ✅ Fix incorrect relationship
                'customItems',           // ✅ Include custom items
                'additionalItems',       // ✅ Include additional items
                'returnDetails.invoiceItem.product' // ✅ Fix return details relation
            ]);

        // Apply date filtering based on rental period, not `created_at`
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

            $invoicesQuery->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('rental_start_date', [$startDate, $endDate])
                      ->orWhereBetween('rental_end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('rental_start_date', '<=', $endDate)
                            ->where('rental_end_date', '>=', $startDate);
                      });
            });
        }

        // Execute the query to get invoices
        $invoices = $invoicesQuery->get();

        return view('customers.rental_details', compact('customer', 'invoices'));
    }

}
