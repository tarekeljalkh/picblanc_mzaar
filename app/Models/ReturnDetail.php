<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',          // ID of the invoice this return belongs to
        'invoice_item_id',     // ID of the specific invoice item
        'additional_item_id',  // ID of the additional item (MISSING - add this)
        'custom_item_id',
        'product_id', // Add product_id here
        'returned_quantity',   // Quantity returned
        'days_used',           // Days the returned items were used
        'cost',                // Cost for the returned items
        'refund',
        'return_date',         // Date of the return
    ];

    protected $casts = [
        'return_date' => 'datetime',
    ];

    // Relationships

    /**
     * Get the invoice item associated with this return detail.
     */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function additionalItem()
    {
        return $this->belongsTo(AdditionalItem::class, 'additional_item_id');
    }

    public function customItem()
{
    return $this->belongsTo(CustomItem::class);
}


    /**
     * Get the invoice associated with this return detail.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // Accessors

    /**
     * Get the formatted cost for display.
     */
    public function getFormattedCostAttribute()
    {
        return number_format($this->cost, 2);
    }
}
