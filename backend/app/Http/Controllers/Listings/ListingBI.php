<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ListingBI extends Controller
{
    public function countListings()
    {
        $listingCount = Listing::where('user_id', Auth::id())->count();

        return response()->json(['listings' => $listingCount]); 
    }

    public function countReservations()
    {
        $userId = Auth::id();
        $reservationsCount = Reservation::whereHas('listing', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        return response()->json(['reservations' => $reservationsCount]);
    }

    public function getEarnings()
    {
        $user = Auth::user()->load('listings.reservations');

        $totalRevenue = 0;

        foreach ($user->listings as $listing) {
            $totalRevenue += $listing->reservations->sum('price');
        }

        return response()->json(['revenue' => $totalRevenue]);
    }

    public function getRevenuePerListing()
    {
        $user = Auth::user()->load('listings.reservations'); 

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
        $user = Auth::user();

        $totalClients = $user->listings()
            ->with('reservations.user')
            ->get()
            ->pluck('reservations')
            ->flatten()
            ->pluck('user_id')
            ->unique()
            ->count();

        return response()->json(['total_clients' => $totalClients]);
    }

    /**
     * Get average revenue per listing (KPI: Revenu moyen par article)
     */
    public function getAverageRevenue()
    {
        $user = Auth::user();
        
        $listings = $user->listings()
            ->with('reservations')
            ->get();
        
        $averageRevenueData = [];
        $totalAverageRevenue = 0;
        $listingsCount = count($listings);
        
        if ($listingsCount > 0) {
            $totalRevenue = 0;
            
            foreach ($listings as $listing) {
                $listingRevenue = $listing->reservations->sum('price');
                $totalRevenue += $listingRevenue;
                
                $reservationCount = $listing->reservations->count();
                $averagePerReservation = $reservationCount > 0 ? $listingRevenue / $reservationCount : 0;
                
                $averageRevenueData[] = [
                    'listing_id' => $listing->id,
                    'listing_name' => $listing->name,
                    'total_revenue' => $listingRevenue,
                    'reservation_count' => $reservationCount,
                    'average_per_reservation' => $averagePerReservation
                ];
            }
            
            $totalAverageRevenue = $totalRevenue / $listingsCount;
        }
        
        return response()->json([
            'average_revenue_per_listing' => $totalAverageRevenue,
            'listings_detail' => $averageRevenueData
        ]);
    }
    
    /**
     * Get occupancy data for listings (KPI: Jours loués vs jours disponibles)
     */
    public function getOccupancy()
    {
        $user = Auth::user();
        
        $listings = $user->listings()
        ->with(['reservations' => function($query) {
            $query->whereBetween('end_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        }])
        ->get();
        
        $occupancyData = [];
        $totalOccupancyRate = 0;
        $listingsCount = count($listings);
        
        if ($listingsCount > 0) {
            $totalOccupiedDays = 0;
            $totalAvailableDays = 0;
            
            foreach ($listings as $listing) {
                // Calculate occupied days (sum of reservation days in last month)
                $occupiedDays = 0;
                foreach ($listing->reservations as $reservation) {
                    $startDate = Carbon::parse($reservation->start_date);
                    $endDate = Carbon::parse($reservation->end_date);
                    $occupiedDays += $startDate->diffInDays($endDate) + 1; 
                }
                
                // Available days 
                $availableDays = Carbon::now()->startOfMonth()->diffInDays(Carbon::now()->endOfMonth()) + 1;

                
                // Calculate occupancy rate for this listing
                $occupancyRate = $availableDays > 0 ? ($occupiedDays / $availableDays) * 100 : 0;
                
                $occupancyData[] = [
                    'listing_id' => $listing->id,
                    'listing_name' => $listing->name,
                    'occupied_days' => $occupiedDays,
                    'available_days' => $availableDays,
                    'occupancy_rate' => round($occupancyRate, 2)
                ];
                
                $totalOccupiedDays += $occupiedDays;
                $totalAvailableDays += $availableDays;
            }
            
            // Calculate overall occupancy rate
            $totalOccupancyRate = $totalAvailableDays > 0 ? 
                round(($totalOccupiedDays / $totalAvailableDays) * 100, 2) : 0;
        }
        
        return response()->json([
            'overall_occupancy_rate' => $totalOccupancyRate,
            'listings_occupancy' => $occupancyData
        ]);
    }
    
    /**
     * Get popularity data for listings (KPI: Nombre de réservations par article)
     */
    public function getPopularItems()
    {
        $user = Auth::user();
        
        $listings = $user->listings()
            ->withCount('reservations')
            ->with(['reservations' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get();
        
        $popularityData = $listings->map(function($listing) {
            return [
                'listing_id' => $listing->id,
                'listing_name' => $listing->name,
                'reservation_count' => $listing->reservations_count,
                'last_reservation' => $listing->reservations->first() ? 
                    $listing->reservations->first()->created_at->format('Y-m-d') : null,
            ];
        })->sortByDesc('reservation_count')->values();
        
        return response()->json([
            'popular_items' => $popularityData
        ]);
    }
    
    /**
     * Get most active clients (KPI: Clients les plus actifs)
     */
    public function getTopClients()
    {
        $user = Auth::user();
        
        $clientReservations = Reservation::with('user')
            ->whereHas('listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->select('user_id', DB::raw('count(*) as reservation_count'), DB::raw('sum(price) as total_spent'))
            ->groupBy('user_id')
            ->orderByDesc('reservation_count')
            ->limit(10)
            ->get();
        
        $topClients = $clientReservations->map(function($reservation) {
            return [
                'client_id' => $reservation->user_id,
                'client_name' => $reservation->user->name ?? 'Unknown',
                'reservation_count' => $reservation->reservation_count,
                'total_spent' => $reservation->total_spent,
            ];
        });
        
        return response()->json([
            'top_clients' => $topClients
        ]);
    }
}