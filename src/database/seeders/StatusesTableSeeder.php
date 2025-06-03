<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => '良好',
        ];
        DB::table('statuses')->insert($param);

        $param = [
            'name' => '目立った傷や汚れなし',
        ];
        DB::table('statuses')->insert($param);

        $param = [
            'name' => 'やや傷や汚れあり',
        ];
        DB::table('statuses')->insert($param);

        $param = [
            'name' => '状態が悪い',
        ];
        DB::table('statuses')->insert($param);
    }
}
