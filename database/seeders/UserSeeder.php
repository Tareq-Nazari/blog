<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Staff;
use App\Customer;
use App\Restaurant;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage restaurants',
            'manage staff',
            'manage menu',
            'manage orders',
            'manage customers',
            'manage reservations',
            'view analytics',
            'manage tables',
            'process payments',
            'kitchen access',
        ];

        // Create roles with explicit guard
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'staff']);
        $waiterRole = Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'staff']);
        $chefRole = Role::firstOrCreate(['name' => 'chef', 'guard_name' => 'staff']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'customer']);

        // Create permissions with guards
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'staff']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'customer']);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::where('guard_name', 'web')->get());
        
        $staffPermissions = Permission::where('guard_name', 'staff')->whereIn('name', [
            'manage staff', 'manage menu', 'manage orders', 
            'manage customers', 'manage reservations', 'view analytics',
            'manage tables', 'process payments'
        ])->get();
        $managerRole->givePermissionTo($staffPermissions);
        
        $waiterPermissions = Permission::where('guard_name', 'staff')->whereIn('name', [
            'manage orders', 'manage customers', 'manage reservations', 'manage tables'
        ])->get();
        $waiterRole->givePermissionTo($waiterPermissions);
        
        $chefPermissions = Permission::where('guard_name', 'staff')->whereIn('name', [
            'manage orders', 'kitchen access'
        ])->get();
        $chefRole->givePermissionTo($chefPermissions);

        // Get restaurant (assuming it exists from RestaurantSeeder)
        $restaurant = Restaurant::first();

        if (!$restaurant) {
            $this->command->error('No restaurant found. Please run RestaurantSeeder first.');
            return;
        }

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@restaurant.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('admin');

        // Create manager staff
        $manager = Staff::firstOrCreate(
            ['email' => 'manager@restaurant.com'],
            [
                'restaurant_id' => $restaurant->id,
                'employee_id' => 'MGR001',
                'first_name' => 'John',
                'last_name' => 'Manager',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0100',
                'position' => 'manager',
                'hire_date' => now()->subMonths(6),
                'hourly_rate' => 25.00,
                'employment_status' => 'active',
                'work_schedule' => [
                    'Monday' => ['start' => '09:00', 'end' => '18:00', 'off' => false],
                    'Tuesday' => ['start' => '09:00', 'end' => '18:00', 'off' => false],
                    'Wednesday' => ['start' => '09:00', 'end' => '18:00', 'off' => false],
                    'Thursday' => ['start' => '09:00', 'end' => '18:00', 'off' => false],
                    'Friday' => ['start' => '09:00', 'end' => '18:00', 'off' => false],
                    'Saturday' => ['start' => '10:00', 'end' => '19:00', 'off' => false],
                    'Sunday' => ['off' => true],
                ],
                'permissions' => ['manage_staff', 'manage_menu', 'view_analytics'],
                'can_login' => true,
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        // Create waiter staff
        $waiter = Staff::firstOrCreate(
            ['email' => 'waiter@restaurant.com'],
            [
                'restaurant_id' => $restaurant->id,
                'employee_id' => 'WTR001',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0101',
                'position' => 'waiter',
                'hire_date' => now()->subMonths(3),
                'hourly_rate' => 15.00,
                'commission_rate' => 2.5,
                'employment_status' => 'active',
                'work_schedule' => [
                    'Monday' => ['off' => true],
                    'Tuesday' => ['start' => '11:00', 'end' => '20:00', 'off' => false],
                    'Wednesday' => ['start' => '11:00', 'end' => '20:00', 'off' => false],
                    'Thursday' => ['start' => '11:00', 'end' => '20:00', 'off' => false],
                    'Friday' => ['start' => '16:00', 'end' => '23:00', 'off' => false],
                    'Saturday' => ['start' => '16:00', 'end' => '23:00', 'off' => false],
                    'Sunday' => ['start' => '10:00', 'end' => '18:00', 'off' => false],
                ],
                'can_login' => true,
                'email_verified_at' => now(),
            ]
        );
        $waiter->assignRole('waiter');

        // Create chef staff
        $chef = Staff::firstOrCreate(
            ['email' => 'chef@restaurant.com'],
            [
                'restaurant_id' => $restaurant->id,
                'employee_id' => 'CHF001',
                'first_name' => 'Marco',
                'last_name' => 'Rodriguez',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0102',
                'position' => 'chef',
                'hire_date' => now()->subYears(2),
                'hourly_rate' => 22.00,
                'employment_status' => 'active',
                'work_schedule' => [
                    'Monday' => ['start' => '10:00', 'end' => '22:00', 'off' => false],
                    'Tuesday' => ['start' => '10:00', 'end' => '22:00', 'off' => false],
                    'Wednesday' => ['off' => true],
                    'Thursday' => ['start' => '10:00', 'end' => '22:00', 'off' => false],
                    'Friday' => ['start' => '10:00', 'end' => '23:00', 'off' => false],
                    'Saturday' => ['start' => '10:00', 'end' => '23:00', 'off' => false],
                    'Sunday' => ['start' => '10:00', 'end' => '21:00', 'off' => false],
                ],
                'permissions' => ['kitchen_access', 'manage_orders'],
                'can_login' => true,
                'email_verified_at' => now(),
            ]
        );
        $chef->assignRole('chef');

        // Create sample customers
        $customers = [
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@email.com',
                'phone' => '+1-555-0200',
                'loyalty_tier' => 'gold',
                'loyalty_points' => 850,
                'total_spent' => 750.00,
                'total_orders' => 15,
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Wilson',
                'email' => 'michael.wilson@email.com',
                'phone' => '+1-555-0201',
                'loyalty_tier' => 'silver',
                'loyalty_points' => 420,
                'total_spent' => 320.00,
                'total_orders' => 8,
            ],
            [
                'first_name' => 'Jessica',
                'last_name' => 'Brown',
                'email' => 'jessica.brown@email.com',
                'phone' => '+1-555-0202',
                'loyalty_tier' => 'bronze',
                'loyalty_points' => 150,
                'total_spent' => 125.00,
                'total_orders' => 3,
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::firstOrCreate(
                ['email' => $customerData['email']],
                array_merge($customerData, [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'marketing_consent' => true,
                    'preferences' => [
                        'dietary_restrictions' => [],
                        'favorite_cuisine' => 'Italian',
                        'spice_preference' => 'medium',
                    ],
                    'last_order_at' => now()->subDays(rand(1, 30)),
                ])
            );
            $customer->assignRole('customer');
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin: admin@restaurant.com / password');
        $this->command->info('Manager: manager@restaurant.com / password');
        $this->command->info('Waiter: waiter@restaurant.com / password');
        $this->command->info('Chef: chef@restaurant.com / password');
        $this->command->info('Customer: emily.davis@email.com / password');
    }
}
