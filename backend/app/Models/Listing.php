<?php

namespace App\Models;

use App\Models\User;
use App\Models\Review;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'price', 
        'description', 
        'images',  
        'category_id', 
        'user_id', 
        'status',
        'address',
        'latitude',
        'longitude',
    ];

    public function setImagesAttribute($value)
    {
        $this->attributes['images'] = is_array($value) 
            ? json_encode($value) 
            : $value;
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

}
