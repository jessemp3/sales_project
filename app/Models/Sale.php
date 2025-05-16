<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['user_id', 'client_id', 'payment_method', 'total_amount'];

    public function user() { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function items() { return $this->hasMany(SaleItem::class); }
    public function installments() { return $this->hasMany(Installment::class); }
}