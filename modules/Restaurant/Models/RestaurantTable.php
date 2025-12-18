<?php

namespace Modules\Restaurant\Models;

use App\Models\Tenant\ModelTenant;

class RestaurantTable extends ModelTenant
{
    public $timestamps = false;

    protected $fillable = [
        'status',
        'products',
        'total',
        'personas',
        'cliente',
        'comentarios',
        'label',
        'shape',
        'environment',
        'waiter',
        'opening_date',
        'order_status',
        'group_id',
        'is_active',
        'original_environment',
    ];


    public function getProductsAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setProductsAttribute($value)
    {
        $this->attributes['products'] = (is_null($value))?null:json_encode($value);
    }

    protected $guarded = [
        'id',
        'group_id',      // Solo se puede modificar mediante métodos específicos
        'is_main_table', // Solo se puede modificar mediante métodos específicos
    ];

    protected $casts = [
        'products' => 'array',
        'total' => 'float',
        'is_active' => 'boolean',
        'opening_date' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(RestaurantTableGroup::class, 'group_id');
    }
}
