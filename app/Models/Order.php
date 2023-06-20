<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function getcreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('n.j.Y h:i A');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
