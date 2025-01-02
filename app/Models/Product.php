<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'type',
    ];

    protected $casts = [
        'type' => ProductType::class,
    ];

    // Relationship to get invoice items for the product
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'product_id');
    }

    public function additionalItems()
{
    return $this->hasMany(AdditionalItem::class, 'product_id', 'id');
}

    // Relationship to get returned items (return details) for the product
    public function returnDetails()
    {
        return $this->hasManyThrough(
            ReturnDetail::class,     // Final target model
            InvoiceItem::class,      // Intermediate model
            'product_id',            // Foreign key on InvoiceItem
            'invoice_item_id',       // Foreign key on ReturnDetail
            'id',                    // Local key on Product
            'id'                     // Local key on InvoiceItem
        );
    }

    // Calculate the total rented quantity (active invoices only)
    public function rentedQuantity()
    {
        return $this->activeRentals()->sum('quantity');
    }

    // Active rentals including invoice status check
    public function activeRentals()
    {
        return $this->invoiceItems()->whereHas('invoice', function ($query) {
            $query->where('status', 'active');
        });
    }

    // Define the relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function rentals()
{
    return $this->hasMany(InvoiceItem::class, 'product_id');
}

}
