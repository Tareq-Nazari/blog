<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Restaurant;
use App\Category;
use App\MenuItem;
use App\Table;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample restaurant
        $restaurant = Restaurant::create([
            'name' => 'Gourmet Bistro',
            'description' => 'A fine dining restaurant offering exquisite cuisine with a modern twist on classic dishes.',
            'email' => 'info@gourmetbistro.com',
            'phone' => '+1-555-0123',
            'address' => '123 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'website' => 'https://gourmetbistro.com',
            'operating_hours' => [
                'Monday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                'Tuesday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                'Wednesday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                'Thursday' => ['open' => '11:00', 'close' => '22:00', 'closed' => false],
                'Friday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
                'Saturday' => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
                'Sunday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
            ],
            'tax_rate' => 8.75,
            'service_charge' => 15.00,
            'currency' => 'USD',
            'settings' => [
                'allow_online_ordering' => true,
                'allow_reservations' => true,
                'auto_confirm_orders' => false,
                'kitchen_printer' => true,
            ],
            'is_active' => true,
        ]);

        // Create categories
        $categories = [
            [
                'name' => 'Appetizers',
                'description' => 'Start your meal with our delicious appetizers',
                'sort_order' => 1,
            ],
            [
                'name' => 'Soups & Salads',
                'description' => 'Fresh soups and crisp salads',
                'sort_order' => 2,
            ],
            [
                'name' => 'Main Courses',
                'description' => 'Our signature main dishes',
                'sort_order' => 3,
            ],
            [
                'name' => 'Seafood',
                'description' => 'Fresh catch of the day',
                'sort_order' => 4,
            ],
            [
                'name' => 'Desserts',
                'description' => 'Sweet endings to your meal',
                'sort_order' => 5,
            ],
            [
                'name' => 'Beverages',
                'description' => 'Refreshing drinks and beverages',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $categoryData) {
            $categoryData['restaurant_id'] = $restaurant->id;
            Category::create($categoryData);
        }

        // Create menu items
        $menuItems = [
            // Appetizers
            [
                'category' => 'Appetizers',
                'name' => 'Truffle Arancini',
                'description' => 'Crispy risotto balls filled with truffle and parmesan',
                'price' => 14.00,
                'cost_price' => 5.50,
                'preparation_time' => 15,
                'is_vegetarian' => true,
                'is_featured' => true,
            ],
            [
                'category' => 'Appetizers',
                'name' => 'Seared Scallops',
                'description' => 'Pan-seared scallops with cauliflower puree',
                'price' => 18.00,
                'cost_price' => 8.00,
                'preparation_time' => 12,
                'is_featured' => true,
            ],
            // Soups & Salads
            [
                'category' => 'Soups & Salads',
                'name' => 'Caesar Salad',
                'description' => 'Classic caesar with homemade croutons and parmesan',
                'price' => 12.00,
                'cost_price' => 4.00,
                'preparation_time' => 8,
                'is_vegetarian' => true,
            ],
            [
                'category' => 'Soups & Salads',
                'name' => 'Lobster Bisque',
                'description' => 'Rich and creamy lobster bisque with cognac',
                'price' => 16.00,
                'cost_price' => 7.00,
                'preparation_time' => 5,
                'spice_level' => 'mild',
            ],
            // Main Courses
            [
                'category' => 'Main Courses',
                'name' => 'Wagyu Beef Tenderloin',
                'description' => 'Premium wagyu beef with roasted vegetables',
                'price' => 65.00,
                'cost_price' => 25.00,
                'preparation_time' => 25,
                'is_featured' => true,
            ],
            [
                'category' => 'Main Courses',
                'name' => 'Herb-Crusted Lamb',
                'description' => 'Rack of lamb with herb crust and mint jus',
                'price' => 45.00,
                'cost_price' => 18.00,
                'preparation_time' => 30,
            ],
            // Seafood
            [
                'category' => 'Seafood',
                'name' => 'Grilled Salmon',
                'description' => 'Atlantic salmon with lemon butter sauce',
                'price' => 28.00,
                'cost_price' => 12.00,
                'preparation_time' => 18,
                'is_gluten_free' => true,
            ],
            [
                'category' => 'Seafood',
                'name' => 'Seafood Paella',
                'description' => 'Traditional Spanish paella with mixed seafood',
                'price' => 35.00,
                'cost_price' => 15.00,
                'preparation_time' => 35,
                'spice_level' => 'medium',
            ],
            // Desserts
            [
                'category' => 'Desserts',
                'name' => 'Chocolate Lava Cake',
                'description' => 'Warm chocolate cake with molten center',
                'price' => 9.00,
                'cost_price' => 3.00,
                'preparation_time' => 12,
                'is_vegetarian' => true,
                'is_featured' => true,
            ],
            [
                'category' => 'Desserts',
                'name' => 'Tiramisu',
                'description' => 'Classic Italian tiramisu with espresso',
                'price' => 8.00,
                'cost_price' => 2.50,
                'preparation_time' => 5,
                'is_vegetarian' => true,
            ],
        ];

        foreach ($menuItems as $itemData) {
            $categoryName = $itemData['category'];
            unset($itemData['category']);
            
            $category = Category::where('name', $categoryName)
                               ->where('restaurant_id', $restaurant->id)
                               ->first();
            
            if ($category) {
                $itemData['restaurant_id'] = $restaurant->id;
                $itemData['category_id'] = $category->id;
                $itemData['ingredients'] = ['Fresh ingredients', 'Organic produce'];
                $itemData['nutritional_info'] = ['calories' => rand(200, 800)];
                MenuItem::create($itemData);
            }
        }

        // Create tables
        $tables = [
            ['number' => '1', 'capacity' => 2, 'type' => 'indoor'],
            ['number' => '2', 'capacity' => 2, 'type' => 'indoor'],
            ['number' => '3', 'capacity' => 4, 'type' => 'indoor'],
            ['number' => '4', 'capacity' => 4, 'type' => 'indoor'],
            ['number' => '5', 'capacity' => 6, 'type' => 'indoor'],
            ['number' => '6', 'capacity' => 6, 'type' => 'indoor'],
            ['number' => '7', 'capacity' => 8, 'type' => 'private'],
            ['number' => 'P1', 'capacity' => 4, 'type' => 'outdoor'],
            ['number' => 'P2', 'capacity' => 4, 'type' => 'outdoor'],
            ['number' => 'B1', 'capacity' => 3, 'type' => 'bar'],
            ['number' => 'B2', 'capacity' => 3, 'type' => 'bar'],
        ];

        foreach ($tables as $index => $tableData) {
            $tableData['restaurant_id'] = $restaurant->id;
            $tableData['description'] = 'Table for ' . $tableData['capacity'] . ' guests';
            $tableData['x_position'] = rand(10, 90);
            $tableData['y_position'] = rand(10, 90);
            Table::create($tableData);
        }

        $this->command->info('Restaurant seeder completed successfully!');
    }
}
