<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;

class AdminBIController extends Controller
{
    public function getTotalClients()
    {
        $clientCount = User::where('role', 'client')->count();
        return response()->json(['total_clients' => $clientCount]);
    }

    public function getTotalLessors()
    {
        $lessorCount = User::where('role', 'lessor')->count();
        return response()->json(['total_lessors' => $lessorCount]);
    }

    public function getTotalCategories()
    {
        $categoryCount = Category::count();
        return response()->json(['total_categories' => $categoryCount]);
    }

    public function getTopLessors()
    {
        $lessors = User::where('role', 'lessor')
            ->withCount('listings')
            ->orderByDesc('listings_count')
            ->take(5)
            ->get(['id', 'name', 'listings_count']);

        return response()->json([
            'lessors' => $lessors->map(function ($lessor) {
                return [
                    'name' => $lessor->name,
                    'listings' => $lessor->listings_count
                ];
            }),
        ]);
    }

    public function getCategories()
    {
        $categories = Category::withCount('listings')
            ->orderByDesc('listings_count')
            ->get(['id', 'cat_title', 'listings_count']);

        return response()->json([
            'categories' => $categories->map(function ($category) {
                return [
                    'title' => $category->cat_title,
                    'count' => $category->listings_count
                ];
            }),
        ]);
    }
}
