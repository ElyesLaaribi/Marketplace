<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Resources\ReservationDateResource;

class ReservationDates extends Controller
{
    public function byListing($listingId)
{
    $reservations = Reservation::where('listing_id', $listingId)->get();
    return ReservationDateResource::collection($reservations);
}

}
