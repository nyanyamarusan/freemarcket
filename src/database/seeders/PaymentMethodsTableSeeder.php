<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => 'コンビニ払い',
        ];
        DB::table('payment_methods')->insert($param);

        $param = [
            'name' => 'カード支払い',
        ];
        DB::table('payment_methods')->insert($param);
    }
}
