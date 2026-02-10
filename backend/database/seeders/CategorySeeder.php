<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key constraints temporarily
        Schema::disableForeignKeyConstraints();
        
        // Clear existing categories
        Category::truncate();
        
        $categories = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'code' => 'DT',
                'name' => 'Desktop',
                'description' => 'Desktop computer systems',
                'is_editable' => false, // System category - not editable by users
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'code' => 'LT',
                'name' => 'Laptop',
                'description' => 'Laptop computer systems',
                'is_editable' => false, // System category - not editable by users
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440003',
                'code' => 'SR',
                'name' => 'Server',
                'description' => 'Server computer systems',
                'is_editable' => false, // System category - not editable by users
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440004',
                'code' => 'MN',
                'name' => 'Monitor',
                'description' => 'Computer monitors and displays',
                'is_editable' => false, // System category - not editable by users
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440005',
                'code' => 'PR',
                'name' => 'Printer',
                'description' => 'Printing devices',
                'is_editable' => true, // User can edit this category
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440006',
                'code' => 'SC',
                'name' => 'Scanner',
                'description' => 'Document scanning devices',
                'is_editable' => true, // User can edit this category
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440007',
                'code' => 'NT',
                'name' => 'Network',
                'description' => 'Network equipment (routers, switches, etc.)',
                'is_editable' => true, // User can edit this category
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440008',
                'code' => 'UP',
                'name' => 'UPS',
                'description' => 'Uninterruptible Power Supply units',
                'is_editable' => true, // User can edit this category
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Update existing inventory records to use new category IDs
        $this->updateExistingInventory();

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    private function updateExistingInventory(): void
    {
        // Mapping of old category names to new category IDs
        $categoryMapping = [
            'Desktop' => '550e8400-e29b-41d4-a716-446655440001',
            'Laptop' => '550e8400-e29b-41d4-a716-446655440002',
            'Server' => '550e8400-e29b-41d4-a716-446655440003',
            'Monitor' => '550e8400-e29b-41d4-a716-446655440004',
        ];

        // Update laboratory inventory
        foreach ($categoryMapping as $categoryName => $categoryId) {
            DB::table('laboratory_inventory')
                ->where('category', $categoryName)
                ->update(['category_id' => $categoryId]);
        }

        // Update department inventory
        foreach ($categoryMapping as $categoryName => $categoryId) {
            DB::table('department_inventory')
                ->where('category', $categoryName)
                ->update(['category_id' => $categoryId]);
        }

        // Update assets
        foreach ($categoryMapping as $categoryName => $categoryId) {
            DB::table('assets')
                ->where('category', $categoryName)
                ->update(['category_id' => $categoryId]);
        }
    }
}
