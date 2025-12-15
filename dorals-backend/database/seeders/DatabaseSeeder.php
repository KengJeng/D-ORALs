<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@dorals.com',
            'password' => Hash::make('password'),
            'phone' => '09123456789',
        ]);

        // Create dental services
        $services = [
            ['name' => 'Oral Examination / Consultation', 'duration' => 30],
            ['name' => 'Dental Cleaning', 'duration' => 45],
            ['name' => 'Tooth Extraction', 'duration' => 60],
            ['name' => 'Tooth Restoration / Filling', 'duration' => 45],
            ['name' => 'Root Canal Treatment', 'duration' => 90],
            ['name' => 'Dental X-Ray', 'duration' => 20],
            ['name' => 'Orthodontic Consultation', 'duration' => 30],
            ['name' => 'Braces Adjustment', 'duration' => 30],
            ['name' => 'Dentures', 'duration' => 60],
            ['name' => 'Dental Whitening', 'duration' => 45],
            ['name' => 'Others', 'duration' => 30],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Email: admin@dorals.com');
        $this->command->info('Admin Password: password');
        $this->command->info('Total Services Created: ' . count($services));
    }
}