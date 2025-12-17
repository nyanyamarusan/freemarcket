<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => '腕時計',
            'price' => 15000,
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'image' => 'Armani+Mens+Clock.jpeg',
            'user_id' => 1,
            'status_id' => 1,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $fashion = Category::where('name', 'ファッション')->first();
        $men = Category::where('name', 'メンズ')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $fashion->id],
            ['item_id' => $itemId, 'category_id' => $men->id],
        ]);

        $param = [
            'name' => 'HDD',
            'price' => 5000,
            'description' => '高速で信頼性の高いハードディスク',
            'image' => 'HDD+Hard+Disk.jpeg',
            'user_id' => 1,
            'status_id' => 2,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $home_appliances = Category::where('name', '家電')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $home_appliances->id],
        ]);

        $param = [
            'name' => '玉ねぎ3束',
            'price' => 300,
            'description' => '新鮮な玉ねぎ3束のセット',
            'image' => 'iLoveIMG+d.jpeg',
            'user_id' => 1,
            'status_id' => 3,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $kitchen = Category::where('name', 'キッチン')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $kitchen->id],
        ]);
        
        $param = [
            'name' => '革靴',
            'price' => 4000,
            'description' => 'クラシックなデザインの革靴',
            'image' => 'Leather+Shoes+Product+Photo.jpeg',
            'user_id' => 1,
            'status_id' => 4,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $fashion = Category::where('name', 'ファッション')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $fashion->id],
        ]);

        $param = [
            'name' => 'ノートPC',
            'price' => 45000,
            'description' => '高性能なノートパソコン',
            'image' => 'Living+Room+Laptop.jpeg',
            'user_id' => 1,
            'status_id' => 1,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $home_appliances = Category::where('name', '家電')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $home_appliances->id],
        ]);

        $param = [
            'name' => 'マイク',
            'price' => 8000,
            'description' => '高音質のレコーディング用マイク',
            'image' => 'Music+Mic+4632231.jpeg',
            'user_id' => 2,
            'status_id' => 2,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $home_appliances = Category::where('name', '家電')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $home_appliances->id],
        ]);

        $param = [
            'name' => 'ショルダーバッグ',
            'price' => 3500,
            'description' => 'おしゃれなショルダーバッグ',
            'image' => 'Purse+fashion+pocket.jpeg',
            'user_id' => 2,
            'status_id' => 3,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $fashion = Category::where('name', 'ファッション')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $fashion->id],
        ]);

        $param = [
            'name' => 'タンブラー',
            'price' => 500,
            'description' => '使いやすいタンブラー',
            'image' => 'Tumbler+souvenir.jpeg',
            'user_id' => 2,
            'status_id' => 4,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $kitchen = Category::where('name', 'キッチン')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $kitchen->id],
        ]);

        $param = [
            'name' => 'コーヒーミル',
            'price' => 4000,
            'description' => '手動のコーヒーミル',
            'image' => 'Waitress+with+Coffee+Grinder.jpeg',
            'user_id' => 2,
            'status_id' => 1,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $kitchen = Category::where('name', 'キッチン')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $kitchen->id],
        ]);

        $param = [
            'name' => 'メイクセット',
            'price' => 2500,
            'description' => '便利なメイクアップセット',
            'image' => '外出メイクアップセット.jpeg',
            'user_id' => 2,
            'status_id' => 2,
        ];
        $itemId = DB::table('items')->insertGetId($param);
        $cosmetics = Category::where('name', 'コスメ')->first();
        DB::table('item_category')->insert([
            ['item_id' => $itemId, 'category_id' => $cosmetics->id],
        ]);
    }
}
