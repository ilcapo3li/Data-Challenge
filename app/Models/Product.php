<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use SoftDeletes, Searchable, HasFactory;

    protected $primaryKey = 'product_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'status',
        'price',
        'currency',
        'image',
        'quantity'
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
    ];

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->product_id;
    }
 
    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return 'product_id';
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'products';
    }

    public function toSearchableArray()
    {
        return [
            'id' => (int) $this->id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'status' => $this->status,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'image' => $this->image,
            'quantity' => (int) $this->quantity
        ];
    }

    public function setImageAttribute($value)
    {
        if ( empty($value) ) { 
            $this->attributes['image'] = 'https://picsum.photos/200';
        } else {
            $this->attributes['image'] = $value;
        }
    }

    public function setSkuAttribute($value)
    {
        if ( empty($value) ) { 
            $this->attributes['sku'] = null;
        } else {
            $this->attributes['sku'] = $value;
        }
    }

    public function setPriceAttribute($value)
    {
        if ( empty($value) ) { 
            $this->attributes['price'] = 0;
        } else {
            $this->attributes['price'] = sprintf("%.2f", $value);
        }
    }

    public function setQuantityAttribute($value)
    {
        if ( empty($value) ) { 
            $this->attributes['quantity'] = 0;
        } else {
            $this->attributes['quantity'] = intval($value);
        }
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }
}
