<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderItem;
use App\Restaurant;
use App\MenuItem;
use App\Table;
use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $restaurant = Restaurant::first(); // Get current restaurant
        
        $query = Order::with(['customer', 'table', 'staff', 'orderItems.menuItem'])
            ->where('restaurant_id', $restaurant->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $orders = $query->latest()->paginate(20);

        $statusCounts = Order::where('restaurant_id', $restaurant->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('orders.index', compact('orders', 'statusCounts', 'restaurant'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $restaurant = Restaurant::first();
        $tables = Table::where('restaurant_id', $restaurant->id)
            ->where('status', 'available')
            ->get();
        $menuItems = MenuItem::with('category')
            ->where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->get()
            ->groupBy('category.name');

        return view('orders.create', compact('restaurant', 'tables', 'menuItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'customer_id' => 'nullable|exists:customers,id',
            'table_id' => 'nullable|exists:tables,id',
            'type' => 'required|in:dine_in,takeaway,delivery,online',
            'special_instructions' => 'nullable|string',
            'customer_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.customizations' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            $restaurant = Restaurant::find($validatedData['restaurant_id']);
            
            // Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'restaurant_id' => $validatedData['restaurant_id'],
                'customer_id' => $validatedData['customer_id'] ?? null,
                'table_id' => $validatedData['table_id'] ?? null,
                'staff_id' => auth()->id(),
                'type' => $validatedData['type'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'special_instructions' => $validatedData['special_instructions'] ?? null,
                'customer_notes' => $validatedData['customer_notes'] ?? null,
                'subtotal' => 0,
                'tax_amount' => 0,
                'service_charge' => 0,
                'total_amount' => 0,
            ]);

            $subtotal = 0;
            $totalPrepTime = 0;

            // Create order items
            foreach ($validatedData['items'] as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                $itemTotal = $menuItem->price * $item['quantity'];
                $subtotal += $itemTotal;
                $totalPrepTime += $menuItem->preparation_time ?? 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $itemTotal,
                    'customizations' => $item['customizations'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'status' => 'pending',
                ]);
            }

            // Calculate totals
            $taxAmount = $subtotal * ($restaurant->tax_rate / 100);
            $serviceCharge = $subtotal * ($restaurant->service_charge / 100);
            $totalAmount = $subtotal + $taxAmount + $serviceCharge;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'total_amount' => $totalAmount,
                'estimated_prep_time' => $totalPrepTime,
            ]);

            // Update table status if applicable
            if ($order->table_id) {
                Table::find($order->table_id)->update(['status' => 'occupied']);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'order' => $order->load('orderItems.menuItem'),
                    'message' => 'Order created successfully'
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'table', 'staff', 'orderItems.menuItem.category', 'restaurant']);
        
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order cannot be edited in current status.');
        }

        $restaurant = $order->restaurant;
        $tables = Table::where('restaurant_id', $restaurant->id)->get();
        $menuItems = MenuItem::with('category')
            ->where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->get()
            ->groupBy('category.name');

        return view('orders.edit', compact('order', 'restaurant', 'tables', 'menuItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order cannot be updated in current status.');
        }

        $validatedData = $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'special_instructions' => 'nullable|string',
            'customer_notes' => 'nullable|string',
            'kitchen_notes' => 'nullable|string',
        ]);

        $order->update($validatedData);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->route('orders.index')
                ->with('error', 'Order cannot be cancelled in current status.');
        }

        DB::beginTransaction();
        
        try {
            // Free up table if applicable
            if ($order->table_id) {
                Table::find($order->table_id)->update(['status' => 'available']);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,served,completed,cancelled'
        ]);

        $newStatus = $validatedData['status'];
        
        // Business logic for status changes
        if (!$this->canChangeStatus($order->status, $newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot change status from {$order->status} to {$newStatus}"
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            $order->updateStatus($newStatus);

            // Handle table status changes
            if ($newStatus === 'completed' && $order->table_id) {
                Table::find($order->table_id)->update(['status' => 'cleaning']);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'order' => $order->fresh(),
                    'message' => 'Order status updated successfully'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Order status updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    /**
     * Kitchen display view
     */
    public function kitchen()
    {
        $restaurant = Restaurant::first();
        
        $orders = Order::with(['table', 'orderItems.menuItem'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['confirmed', 'preparing'])
            ->orderBy('confirmed_at')
            ->get();

        return view('orders.kitchen', compact('orders', 'restaurant'));
    }

    /**
     * Get pending orders for AJAX
     */
    public function pendingOrders()
    {
        $restaurant = Restaurant::first();
        
        $orders = Order::with(['customer', 'table', 'orderItems.menuItem'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing'])
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Public order form (for QR codes, online ordering)
     */
    public function publicOrder(Restaurant $restaurant)
    {
        $menuItems = MenuItem::with('category')
            ->where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->get()
            ->groupBy('category.name');

        return view('orders.public', compact('restaurant', 'menuItems'));
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', today())->latest()->first();
        $sequence = $lastOrder ? (int)substr($lastOrder->order_number, -4) + 1 : 1;
        
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if status change is allowed
     */
    private function canChangeStatus($currentStatus, $newStatus)
    {
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['served'],
            'served' => ['completed'],
            'completed' => [],
            'cancelled' => []
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
}
