<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'item_id' => 1,
            'seller_id' => 1,
            'buyer_id' => 2,
            'status' => 'in_progress',
            'latest_message_at' => '2025-12-16 14:00:00',
        ];
        DB::table('transactions')->insert($param);

        $param = [
            'item_id' => 10,
            'seller_id' => 2,
            'buyer_id' => 1,
            'status' => 'in_progress',
            'latest_message_at' => '2025-12-17 14:00:00',
        ];
        DB::table('transactions')->insert($param);

        $param = [
            'item_id' => 2,
            'seller_id' => 1,
            'buyer_id' => 2,
            'status' => 'in_progress',
        ];
        DB::table('transactions')->insert($param);
    }
}
