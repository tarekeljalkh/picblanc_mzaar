<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',      // ID of the associated invoice
        'product_id',      // ID of the associated product
        'quantity',        // Quantity of the added item
        'price',           // Price per unit
        'days',
        'paid',
        'total_price',     // Total price for the added items
        'rental_start_date', // Rental start date
        'rental_end_date',   // Rental end date
    ];

    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
    ];

    // Relationships

    /**
     * Get the invoice associated with this additional item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the product associated with this additional item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors

    /**
     * Get the formatted total price for display.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2);
    }

    // Core Methods

    /**
     * Dynamically calculate and update the total price based on quantity and unit price.
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->price * $this->quantity;
        return $this->total_price;
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
