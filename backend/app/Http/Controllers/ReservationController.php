<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Resources\ReservationResource;
use App\Http\Requests\StoreReservationRequest;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $me = auth()->user();

        $reservations = Reservation::with(['user','listing.user'])
            ->where(function($q) use ($me) {
                $q->where('user_id', $me->id)           
                  ->orWhereHas('listing', fn($q2) =>
                      $q2->where('user_id', $me->id)
                  );
            })
            ->get();
        return ReservationResource::collection($reservations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();
        $listingId = $validated['listing_id'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

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
        $this->authorize('view', $reservation);
        return ReservationResource::make($reservation);
    }

}