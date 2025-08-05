@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h3 class="mb-2">{{ $stats['today']['orders'] }}</h3>
                <p class="mb-1">Today's Orders</p>
                <small><i class="fas fa-chart-up"></i> Today</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card success">
            <div class="card-body text-center">
                <h3 class="mb-2">${{ number_format($stats['today']['revenue'], 2) }}</h3>
                <p class="mb-1">Today's Revenue</p>
                <small><i class="fas fa-dollar-sign"></i> Today</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card warning">
            <div class="card-body text-center">
                <h3 class="mb-2">{{ $stats['pending_orders'] }}</h3>
                <p class="mb-1">Pending Orders</p>
                <small><i class="fas fa-clock"></i> Active</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card info">
            <div class="card-body text-center">
                <h3 class="mb-2">{{ $stats['available_tables'] }}/{{ $stats['total_tables'] }}</h3>
                <p class="mb-1">Available Tables</p>
                <small><i class="fas fa-chair"></i> Now</small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Reservations -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5><i class="fas fa-shopping-cart"></i> Recent Orders</h5>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Table</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td><a href="{{ route('orders.show', $order) }}">#{{ $order->order_number }}</a></td>
                                <td>{{ $order->customer ? $order->customer->full_name : 'Walk-in' }}</td>
                                <td>{{ $order->table ? 'Table '.$order->table->number : '-' }}</td>
                                <td><span class="badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">No orders today.</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5><i class="fas fa-calendar-check"></i> Today's Reservations</h5>
                <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                @if($todayReservations->count() > 0)
                @foreach($todayReservations as $reservation)
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                    <div>
                        <strong>{{ $reservation->customer_name }}</strong><br>
                        <small class="text-muted">{{ $reservation->party_size }} guests</small>
                    </div>
                    <div class="text-end">
                        <span class="badge status-{{ $reservation->status }}">{{ ucfirst($reservation->status) }}</span><br>
                        <small>{{ $reservation->reservation_date->format('H:i') }}</small>
                    </div>
                </div>
                @endforeach
                @else
                <p class="text-muted text-center">No reservations today.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Table Status Overview -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chair"></i> Table Status Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <div class="p-3 border rounded bg-success text-white">
                            <h4>{{ $tableStats['available'] }}</h4>
                            <small>Available</small>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="p-3 border rounded bg-danger text-white">
                            <h4>{{ $tableStats['occupied'] }}</h4>
                            <small>Occupied</small>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="p-3 border rounded bg-warning text-white">
                            <h4>{{ $tableStats['reserved'] }}</h4>
                            <small>Reserved</small>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="p-3 border rounded bg-info text-white">
                            <h4>{{ $tableStats['cleaning'] }}</h4>
                            <small>Cleaning</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <a href="{{ route('floor-plan') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-map"></i> View Floor Plan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection