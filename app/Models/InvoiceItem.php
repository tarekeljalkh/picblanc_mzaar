<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
        'rental_start_date',
        'rental_end_date',
        'days',
        'paid',
        'returned_quantity',
        'added_quantity',
    ];

    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returnDetails()
    {
        return $this->hasMany(ReturnDetail::class, 'invoice_item_id');
    }

    // Helper Methods

    /**
     * Calculate cost for the given quantity and days
     */
    public function calculateCost($quantity, $days)
    {
        return $this->price * $quantity * $days;
    }

    /**
     * Get total cost of all returns
     */
    public function getTotalReturnCostAttribute()
    {
        return $this->returnDetails->sum('cost');
    }

    /**
     * Get total quantity returned
     */
    public function getTotalReturnedQuantityAttribute()
    {
        return $this->returnDetails->sum('returned_quantity');
    }

    // Core Methods

    /**
     * Process a return for this invoice item
     */
    public function processReturn($returnedQuantity, $returnDate)
    {
        // Validate returned quantity
        if ($returnedQuantity > ($this->quantity - $this->returned_quantity)) {
            throw new \Exception("Returned quantity exceeds the remaining quantity.");
        }

        // Calculate days used
        $usedDays = max(Carbon::parse($this->rental_start_date)->diffInDays($returnDate), 1);

        // Calculate cost for the return
        $usedCost = $this->calculateCost($returnedQuantity, $usedDays);

        // Save the return details
        ReturnDetail::create([
            'invoice_item_id' => $this->id,
            'returned_quantity' => $returnedQuantity,
            'days_used' => $usedDays,
            'cost' => $usedCost,
            'return_date' => $returnDate->toDateString(),
        ]);

        // Update returned quantity
        $this->returned_quantity += $returnedQuantity;

        // If all quantities for this item are returned, update its status
        if ($this->returned_quantity == $this->quantity) {
            $this->status = 'returned';
        }

        $this->save();

        // Trigger invoice status update
        $this->invoice->checkAndUpdateStatus();

        return [
            'used_cost' => $usedCost,
            'remaining_quantity' => $this->quantity - $this->returned_quantity,
        ];
    }


    /**
     * Add quantity to the invoice item
     */
    public function addQuantity($addedQuantity, $startDate)
    {
        // Update added quantity
        $this->added_quantity += $addedQuantity;

        // Calculate days for the added quantity
        $totalDays = max(Carbon::parse($startDate)->diffInDays($this->rental_end_date), 1);

        // Calculate the cost of the added quantity
        $newCost = $this->calculateCost($addedQuantity, $totalDays);

        // Save changes
        $this->save();

        return $newCost;
    }

    public function getTotalPriceAttribute()
    {
        if ($this->invoice->category->name === 'daily') {
            return $this->price * $this->quantity * $this->days;
        }
        return $this->price * $this->quantity;
    }

    public function getFormattedStartDateAttribute()
    {
        return $this->rental_start_date ? $this->rental_start_date->format('d/m/Y h:i A') : 'N/A';
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->rental_end_date ? $this->rental_end_date->format('d/m/Y h:i A') : 'N/A';
    }
}
