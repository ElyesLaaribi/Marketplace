<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBIController extends Controller
{
    /**
     * Get total number of clients
     */
    public function getTotalClients()
    {
        $clientCount = User::where('role', 'client')->count();
        
        return response()->json(['total_clients' => $clientCount]);
    }
    
    /**
     * Get total number of lessors
     */
    public function getTotalLessors()
    {
        $lessorCount = User::where('role', 'lessor')->count();
        
        return response()->json(['total_lessors' => $lessorCount]);
    }
    
    /**
     * Get total number of categories
     */
    public function getTotalCategories()
    {
        $categoryCount = Category::count();
        
        return response()->json(['total_categories' => $categoryCount]);
    }
    
    /**
     * Get top 5 lessors by number of listings
     */
    public function getTopLessors()
    {
        $lessors = User::where('role', 'lessor')
            ->withCount('listings')
            ->orderByDesc('listings_count')
            ->take(5)
            ->get(['id', 'name', 'listings_count']);
        
        $labels = [];
        $data = [];
        $colors = $this->generateColorArray(count($lessors));
        
        foreach ($lessors as $lessor) {
            $labels[] = $lessor->name;
            $data[] = $lessor->listings_count;
        }
        
        return response()->json([
            'lessors' => $lessors->map(function($lessor) {
                return [
                    'name' => $lessor->name,
                    'listings' => $lessor->listings_count
                ];
            }),
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Listings',
                        'backgroundColor' => $colors,
                        'data' => $data
                    ]
                ]
            ]
        ]);
    }
    
    /**
     * Get categories distribution
     */
    public function getCategories()
    {
        $categories = Category::withCount('listings')
            ->orderByDesc('listings_count')
            ->get(['id', 'cat_title', 'listings_count']);
        
        $labels = [];
        $data = [];
        $colors = $this->generateColorArray(count($categories));
        
        foreach ($categories as $category) {
            $labels[] = $category->cat_title;
            $data[] = $category->listings_count;
        }
        
        return response()->json([
            'categories' => $categories->map(function($category) {
                return [
                    'cat_title' => $category->cat_title,
                    'count' => $category->listings_count
                ];
            }),
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'hoverOffset' => 4
                    ]
                ]
            ]
        ]);
    }
    
    /**
     * Generate an array of distinct colors for charts
     */
    private function generateColorArray($count) {
        $colors = [
            '#4CAF50', '#2196F3', '#9C27B0', '#F44336', '#FF9800', 
            '#795548', '#3F51B5', '#00BCD4', '#FFEB3B', '#009688',
            '#673AB7', '#FFC107', '#607D8B', '#E91E63', '#8BC34A'
        ];
        
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }
        
        return $result;
    }
} 