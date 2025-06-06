<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = ['listing_id', 'user_id', 'start_date', 'end_date', 'price' ,'reminder_sent', 'reminder_sent_at'];
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
      ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}