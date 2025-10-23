<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'recovery_email' => 'recovery_admin@example.com',
            ]);

        $admin->assignRole('Admin');

        Staff::factory()
            ->for($admin, 'user')
            ->create([
                'email' => $admin->email,
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'job_title' => 'Super Administrator',
                'status' => 'active',
            ]);

        $samples = [
            [
                'role' => 'Manager',
                'name' => 'Morgan Manager',
                'email' => 'manager@example.com',
                'first_name' => 'Morgan',
                'last_name' => 'Manager',
                'job_title' => 'Operations Manager',
                'status' => 'active',
            ],
            [
                'role' => 'Technician',
                'name' => 'Taylor Technician',
                'email' => 'technician@example.com',
                'first_name' => 'Taylor',
                'last_name' => 'Technician',
                'job_title' => 'Field Technician',
                'status' => 'active',
            ],
            [
                'role' => 'Staff',
                'name' => 'Sydney Staff',
                'email' => 'staff@example.com',
                'first_name' => 'Sydney',
                'last_name' => 'Staff',
                'job_title' => 'Support Specialist',
                'status' => 'active',
            ],
            [
                'role' => 'Auditor',
                'name' => 'Avery Auditor',
                'email' => 'auditor@example.com',
                'first_name' => 'Avery',
                'last_name' => 'Auditor',
                'job_title' => 'Compliance Auditor',
                'status' => 'active',
            ],
            [
                'role' => 'ReadOnly',
                'name' => 'Riley Readonly',
                'email' => 'readonly@example.com',
                'first_name' => 'Riley',
                'last_name' => 'Readonly',
                'job_title' => 'Reporting Analyst',
                'status' => 'inactive',
            ],
        ];

        foreach ($samples as $sample) {
            $user = User::factory()
                ->withoutTwoFactor()
                ->create([
                    'name' => $sample['name'],
                    'email' => $sample['email'],
                    'recovery_email' => 'recovery_' . strtolower(str_replace(' ', '', $sample['role'])) . '@example.com',
                ]);

            $user->assignRole($sample['role']);

            Staff::factory()
                ->for($user, 'user')
                ->create([
                    'email' => $sample['email'],
                    'first_name' => $sample['first_name'],
                    'last_name' => $sample['last_name'],
                    'job_title' => $sample['job_title'],
                    'status' => $sample['status'],
                ]);
        }
    }
}
