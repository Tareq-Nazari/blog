<?php

namespace App\Http\Controllers;

use App\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $restaurants = Restaurant::with('staff')->paginate(10);
        return view('restaurants.index', compact('restaurants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('restaurants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => 'required|email|unique:restaurants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'service_charge' => 'required|numeric|min:0|max:100',
            'currency' => 'required|string|size:3',
            
            // Operating hours
            'monday_open' => 'nullable|date_format:H:i',
            'monday_close' => 'nullable|date_format:H:i',
            'monday_closed' => 'boolean',
            'tuesday_open' => 'nullable|date_format:H:i',
            'tuesday_close' => 'nullable|date_format:H:i',
            'tuesday_closed' => 'boolean',
            'wednesday_open' => 'nullable|date_format:H:i',
            'wednesday_close' => 'nullable|date_format:H:i',
            'wednesday_closed' => 'boolean',
            'thursday_open' => 'nullable|date_format:H:i',
            'thursday_close' => 'nullable|date_format:H:i',
            'thursday_closed' => 'boolean',
            'friday_open' => 'nullable|date_format:H:i',
            'friday_close' => 'nullable|date_format:H:i',
            'friday_closed' => 'boolean',
            'saturday_open' => 'nullable|date_format:H:i',
            'saturday_close' => 'nullable|date_format:H:i',
            'saturday_closed' => 'boolean',
            'sunday_open' => 'nullable|date_format:H:i',
            'sunday_close' => 'nullable|date_format:H:i',
            'sunday_closed' => 'boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // Build operating hours array
        $operatingHours = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            $dayLower = strtolower($day);
            $operatingHours[$day] = [
                'open' => $request->input("{$dayLower}_open"),
                'close' => $request->input("{$dayLower}_close"),
                'closed' => $request->boolean("{$dayLower}_closed")
            ];
        }
        
        $validatedData['operating_hours'] = $operatingHours;

        // Default settings
        $validatedData['settings'] = [
            'allow_online_ordering' => $request->boolean('allow_online_ordering'),
            'allow_reservations' => $request->boolean('allow_reservations'),
            'auto_confirm_orders' => $request->boolean('auto_confirm_orders'),
            'kitchen_printer' => $request->boolean('kitchen_printer'),
        ];

        $restaurant = Restaurant::create($validatedData);

        return redirect()->route('restaurants.show', $restaurant)
            ->with('success', 'Restaurant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['categories.menuItems', 'tables', 'staff']);
        
        // Get recent statistics
        $stats = [
            'total_orders' => $restaurant->orders()->count(),
            'total_revenue' => $restaurant->orders()->where('status', 'completed')->sum('total_amount'),
            'total_customers' => $restaurant->orders()->distinct('customer_id')->count('customer_id'),
            'average_order_value' => $restaurant->orders()->where('status', 'completed')->avg('total_amount'),
            'menu_items_count' => $restaurant->menuItems()->count(),
            'tables_count' => $restaurant->tables()->count(),
            'staff_count' => $restaurant->staff()->count(),
        ];

        return view('restaurants.show', compact('restaurant', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant)
    {
        return view('restaurants.edit', compact('restaurant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => ['required', 'email', Rule::unique('restaurants')->ignore($restaurant->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'service_charge' => 'required|numeric|min:0|max:100',
            'currency' => 'required|string|size:3',
            
            // Operating hours
            'monday_open' => 'nullable|date_format:H:i',
            'monday_close' => 'nullable|date_format:H:i',
            'monday_closed' => 'boolean',
            'tuesday_open' => 'nullable|date_format:H:i',
            'tuesday_close' => 'nullable|date_format:H:i',
            'tuesday_closed' => 'boolean',
            'wednesday_open' => 'nullable|date_format:H:i',
            'wednesday_close' => 'nullable|date_format:H:i',
            'wednesday_closed' => 'boolean',
            'thursday_open' => 'nullable|date_format:H:i',
            'thursday_close' => 'nullable|date_format:H:i',
            'thursday_closed' => 'boolean',
            'friday_open' => 'nullable|date_format:H:i',
            'friday_close' => 'nullable|date_format:H:i',
            'friday_closed' => 'boolean',
            'saturday_open' => 'nullable|date_format:H:i',
            'saturday_close' => 'nullable|date_format:H:i',
            'saturday_closed' => 'boolean',
            'sunday_open' => 'nullable|date_format:H:i',
            'sunday_close' => 'nullable|date_format:H:i',
            'sunday_closed' => 'boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($restaurant->logo) {
                Storage::disk('public')->delete($restaurant->logo);
            }
            $validatedData['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // Build operating hours array
        $operatingHours = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            $dayLower = strtolower($day);
            $operatingHours[$day] = [
                'open' => $request->input("{$dayLower}_open"),
                'close' => $request->input("{$dayLower}_close"),
                'closed' => $request->boolean("{$dayLower}_closed")
            ];
        }
        
        $validatedData['operating_hours'] = $operatingHours;

        // Update settings
        $currentSettings = $restaurant->settings ?? [];
        $validatedData['settings'] = array_merge($currentSettings, [
            'allow_online_ordering' => $request->boolean('allow_online_ordering'),
            'allow_reservations' => $request->boolean('allow_reservations'),
            'auto_confirm_orders' => $request->boolean('auto_confirm_orders'),
            'kitchen_printer' => $request->boolean('kitchen_printer'),
        ]);

        $restaurant->update($validatedData);

        return redirect()->route('restaurants.show', $restaurant)
            ->with('success', 'Restaurant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        // Check if restaurant has orders or other dependencies
        if ($restaurant->orders()->exists()) {
            return redirect()->route('restaurants.index')
                ->with('error', 'Cannot delete restaurant with existing orders.');
        }

        // Delete logo file
        if ($restaurant->logo) {
            Storage::disk('public')->delete($restaurant->logo);
        }

        $restaurant->delete();

        return redirect()->route('restaurants.index')
            ->with('success', 'Restaurant deleted successfully.');
    }

    /**
     * Toggle restaurant active status
     */
    public function toggleStatus(Restaurant $restaurant)
    {
        $restaurant->update(['is_active' => !$restaurant->is_active]);
        
        $status = $restaurant->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Restaurant {$status} successfully.");
    }
}
