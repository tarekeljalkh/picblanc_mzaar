<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'name',
        'description',
        'price',
        'quantity',
        'returned_quantity',
        'status',
        'rental_start_date',
        'rental_end_date',
        'days',
        ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function returnDetails()
{
    return $this->hasMany(ReturnDetail::class);
}

}
