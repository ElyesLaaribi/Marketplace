<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Reservation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();
        
        // Convert product_id to listing_id for database consistency
        $listingId = $validated['listing_id'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // Check for overlapping reservations
        $overlappingReservations = Reservation::where('listing_id', $listingId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlappingReservations) {
            return response()->json(['message' => 'The listing is already reserved for the selected dates.'], 409);
        }
    
        $reservation = Reservation::create([
            'listing_id' => $listingId,
            'user_id' => auth()->id() ?? 1,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    
        return response()->json(['message' => 'Reservation created successfully.', 'reservation' => $reservation], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        return response()->json($reservation);
    }

    // Other methods...
}