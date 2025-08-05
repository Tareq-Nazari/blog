<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RestaurantSeeder::class,
            UserSeeder::class,
        ]);
        
        $this->command->info('Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('Default Login Credentials:');
        $this->command->info('=========================');
        $this->command->info('Admin: admin@restaurant.com / password');
        $this->command->info('Manager: manager@restaurant.com / password');
        $this->command->info('Waiter: waiter@restaurant.com / password');
        $this->command->info('Chef: chef@restaurant.com / password');
        $this->command->info('Customer: emily.davis@email.com / password');
    }
}
