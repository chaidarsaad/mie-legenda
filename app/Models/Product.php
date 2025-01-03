<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{

    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'category',
        'image',
        'is_best_seller'
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = str()->slug($value . '-' . Str::random(4));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setCategoryIdAttribute($value)
    {
        // Mengatur nilai category_id
        $this->attributes['category_id'] = $value;

        // Menyimpan nama kategori ke dalam kolom category
        if ($value) {
            $category = Category::find($value);

            // Jika kategori ditemukan, set kolom category
            if ($category) {
                $this->attributes['category'] = $category->name;

                // Pastikan kolom category juga diupdate di database
                $this->save();
            }
        }
        // Jika category_id dihapus atau null, Anda bisa set category ke null
        else {
            $this->attributes['category'] = null;
            $this->save();
        }
    }
}
