<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mode = config('tdmv.seed_mode', 'demo');

        if ($mode === 'live') {
            $this->call(LiveDataSeeder::class);

            return;
        }

        $this->call(DemoDataSeeder::class);
    }
}
