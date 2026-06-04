<?php

namespace Database\Seeders;

use App\Models\TaskAiPlan;
use Illuminate\Database\Seeder;

class TaskAiPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['code' => 'daily', 'name' => 'Daily', 'price' => 500, 'duration_days' => 1, 'sort_order' => 10],
            ['code' => 'weekly', 'name' => 'Weekly', 'price' => 1500, 'duration_days' => 7, 'sort_order' => 20],
            ['code' => 'monthly', 'name' => 'Monthly', 'price' => 3000, 'duration_days' => 30, 'sort_order' => 30],
            ['code' => 'six_months', 'name' => '6 Months', 'price' => 10000, 'duration_days' => 180, 'sort_order' => 40],
            ['code' => 'yearly', 'name' => 'Yearly', 'price' => 15000, 'duration_days' => 365, 'sort_order' => 50],
        ];

        foreach ($plans as $plan) {
            TaskAiPlan::updateOrCreate(
                ['code' => $plan['code']],
                $plan,
            );
        }

        $this->command->info('Default Task AI plans seeded.');
    }
}
