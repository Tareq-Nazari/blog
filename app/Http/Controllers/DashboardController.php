<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Restaurant;
use App\Order;
use App\Customer;
use App\MenuItem;
use App\Table;
use App\Reservation;
use App\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get current restaurant (for now, use the first one)
        $restaurant = Restaurant::first();
        
        if (!$restaurant) {
            return redirect()->route('admin.restaurants.create')
                ->with('warning', 'Please create a restaurant first.');
        }

        // Dashboard statistics
        $stats = $this->getDashboardStats($restaurant);
        
        // Recent orders
        $recentOrders = Order::with(['customer', 'table', 'orderItems.menuItem'])
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->take(10)
            ->get();
            
        // Today's reservations
        $todayReservations = Reservation::with(['customer', 'table'])
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('reservation_date', today())
            ->orderBy('reservation_date')
            ->get();
            
        // Table status overview
        $tableStats = $this->getTableStats($restaurant);

        return view('dashboard.index', compact(
            'restaurant', 
            'stats', 
            'recentOrders', 
            'todayReservations',
            'tableStats'
        ));
    }

    public function analytics()
    {
        $restaurant = Restaurant::first();
        
        if (!$restaurant) {
            return redirect()->route('dashboard')
                ->with('error', 'No restaurant found.');
        }

        // Sales analytics
        $salesData = $this->getSalesAnalytics($restaurant);
        
        // Popular menu items
        $popularItems = $this->getPopularMenuItems($restaurant);
        
        // Customer analytics
        $customerAnalytics = $this->getCustomerAnalytics($restaurant);
        
        // Revenue trends
        $revenueTrends = $this->getRevenueTrends($restaurant);

        return view('dashboard.analytics', compact(
            'restaurant',
            'salesData',
            'popularItems',
            'customerAnalytics',
            'revenueTrends'
        ));
    }

    public function dashboardStats()
    {
        $restaurant = Restaurant::first();
        return response()->json($this->getDashboardStats($restaurant));
    }

    public function systemSettings()
    {
        $totalRestaurants = Restaurant::count();
        $totalStaff = Staff::count();
        $totalCustomers = Customer::count();
        $totalOrders = Order::count();
        
        $systemStats = [
            'restaurants' => $totalRestaurants,
            'staff' => $totalStaff,
            'customers' => $totalCustomers,
            'orders' => $totalOrders,
        ];

        return view('admin.system-settings', compact('systemStats'));
    }

    private function getDashboardStats($restaurant)
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'orders' => Order::where('restaurant_id', $restaurant->id)
                    ->whereDate('created_at', $today)
                    ->count(),
                'revenue' => Order::where('restaurant_id', $restaurant->id)
                    ->whereDate('created_at', $today)
                    ->where('status', 'completed')
                    ->sum('total_amount'),
                'customers' => Order::where('restaurant_id', $restaurant->id)
                    ->whereDate('created_at', $today)
                    ->distinct('customer_id')
                    ->count('customer_id'),
                'reservations' => Reservation::where('restaurant_id', $restaurant->id)
                    ->whereDate('reservation_date', $today)
                    ->count(),
            ],
            'week' => [
                'orders' => Order::where('restaurant_id', $restaurant->id)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
                'revenue' => Order::where('restaurant_id', $restaurant->id)
                    ->where('created_at', '>=', $thisWeek)
                    ->where('status', 'completed')
                    ->sum('total_amount'),
            ],
            'month' => [
                'orders' => Order::where('restaurant_id', $restaurant->id)
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
                'revenue' => Order::where('restaurant_id', $restaurant->id)
                    ->where('created_at', '>=', $thisMonth)
                    ->where('status', 'completed')
                    ->sum('total_amount'),
            ],
            'pending_orders' => Order::where('restaurant_id', $restaurant->id)
                ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                ->count(),
            'available_tables' => Table::where('restaurant_id', $restaurant->id)
                ->where('status', 'available')
                ->count(),
            'total_tables' => Table::where('restaurant_id', $restaurant->id)
                ->count(),
        ];
    }

    private function getTableStats($restaurant)
    {
        $tables = Table::where('restaurant_id', $restaurant->id)->get();
        
        return [
            'total' => $tables->count(),
            'available' => $tables->where('status', 'available')->count(),
            'occupied' => $tables->where('status', 'occupied')->count(),
            'reserved' => $tables->where('status', 'reserved')->count(),
            'cleaning' => $tables->where('status', 'cleaning')->count(),
        ];
    }

    private function getSalesAnalytics($restaurant)
    {
        $last30Days = Carbon::now()->subDays(30);
        
        return [
            'total_sales' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'total_orders' => Order::where('restaurant_id', $restaurant->id)
                ->count(),
            'average_order_value' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', 'completed')
                ->avg('total_amount'),
            'last_30_days_sales' => Order::where('restaurant_id', $restaurant->id)
                ->where('created_at', '>=', $last30Days)
                ->where('status', 'completed')
                ->sum('total_amount'),
        ];
    }

    private function getPopularMenuItems($restaurant)
    {
        return DB::table('order_items')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.restaurant_id', $restaurant->id)
            ->where('orders.status', 'completed')
            ->select(
                'menu_items.name',
                'menu_items.price',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name', 'menu_items.price')
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get();
    }

    private function getCustomerAnalytics($restaurant)
    {
        $totalCustomers = Customer::whereHas('orders', function($query) use ($restaurant) {
            $query->where('restaurant_id', $restaurant->id);
        })->count();

        $loyaltyTiers = Customer::whereHas('orders', function($query) use ($restaurant) {
            $query->where('restaurant_id', $restaurant->id);
        })->select('loyalty_tier', DB::raw('count(*) as count'))
        ->groupBy('loyalty_tier')
        ->get();

        return [
            'total_customers' => $totalCustomers,
            'loyalty_distribution' => $loyaltyTiers,
            'repeat_customers' => Customer::whereHas('orders', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })->where('total_orders', '>', 1)->count(),
        ];
    }

    private function getRevenueTrends($restaurant)
    {
        $last7Days = collect();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Order::where('restaurant_id', $restaurant->id)
                ->whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
                
            $last7Days->push([
                'date' => $date->format('M d'),
                'revenue' => $revenue
            ]);
        }

        return $last7Days;
    }
}
