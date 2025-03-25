<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description', 'image', 'category_id',  'status'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}