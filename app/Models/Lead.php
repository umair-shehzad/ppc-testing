<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public function setDescriptionAttribute($value)
    {
        return $this->attributes['description'] = json_encode($value);
    }

    public function getDescriptionAttribute($value)
    {
        return json_decode($value);
    }

    public function getcreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d F Y, h:i A');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
