<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vehicles',
    ];

    protected $casts = [
        'vehicles' => 'array',
    ];

    public function travelOrders()
    {
        return $this->hasMany(TravelOrder::class);
    }

    public function getVehicleOptionsAttribute()
    {
        if (!$this->vehicles) {
            return [];
        }

        $options = [];
        foreach ($this->vehicles as $vehicle) {
            $options["{$vehicle['car_name']}|{$vehicle['plate_number']}"] =
                "{$vehicle['car_name']} - {$vehicle['plate_number']}";
        }
        return $options;
    }
}
