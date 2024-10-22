<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orderconfirm extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_name',
        'customer_contact',
        'customer_email',
        'invoice_number',
        'payment_mode',
        'coach_berth',
        'train',
        'delivery_station',
        'item_description',
        'quantity',
        'price',
        'gst',
        'total',
    ];
}
