<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ListingBI extends Controller
{
    public function countListings()
    {
        $listingCount = Listing::where('user_id', auth()->id())->count();

        return response()->json(['listings' => $listingCount]);
    }

    public function countReservations()
    {
        $userID = auth()->user();
        $reservationsCount = Reservation::with(['user','listing.user'])
            ->where(function($q) use ($userID) {
                $q->where('user_id', $userID->id)           
                  ->orWhereHas('listing', fn($q2) =>
                      $q2->where('user_id', $userID->id)
                  );
            })
            ->count();

        return response()->json(['reservations' => $reservationsCount]);
    }

    public function getEarnings()
    {
        $user = auth()->user()->load('listings.reservations');

        $totalRevenue = 0;

        foreach ($user->listings as $listing) {
            $totalRevenue += $listing->reservations->sum('price');
        }

        return response()->json(['revenue' => $totalRevenue]);
    }

    public function getRevenuePerListing()
    {
        $user = auth()->user()->load('listings.reservations'); 

        $revenuePerListing = [];

        foreach ($user->listings as $listing) {
            $listingRevenue = $listing->reservations->sum('price');
                    $revenuePerListing[] = [
                'listing_id' => $listing->id,
                'listing_name' => $listing->name,
                'revenue' => $listingRevenue,
            ];
        }

        return response()->json(['revenue_per_listing' => $revenuePerListing]);
    }
    
    public function getTotalClients()
    {
        $user = auth()->user();

        $totalClients = $user->listings()
            ->with('reservations')
            ->get()
            ->pluck('reservations')
            ->flatten()
            ->unique('user_id')
            ->count();

        return response()->json(['total_clients' => $totalClients]);
    }

}
