<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {

        // التصنيفات
        $solarPanels = Category::create([
            'name' => 'الألواح الشمسية'
        ]);

        $batteries = Category::create([
            'name' => 'البطاريات'
        ]);

        $inverters = Category::create([
            'name' => 'الانفيرترات'
        ]);


        // منتجات الألواح
        Product::create([
            'name' => 'لوح شمسي 550 واط',
            'description' => 'لوح شمسي عالي الكفاءة 550 واط',
            'price' => 120,
            'stock' => 20,
            'image' => 'images/panel_550.jpg',
            'category_id' => $solarPanels->id
        ]);

        Product::create([
            'name' => 'لوح شمسي 450 واط',
            'description' => 'لوح شمسي مناسب للأنظمة المنزلية',
            'price' => 90,
            'stock' => 30,
            'image' => 'images/panel_450.jpg',
            'category_id' => $solarPanels->id
        ]);


        // منتجات البطاريات
        Product::create([
            'name' => 'بطارية ليثيوم 200Ah',
            'description' => 'بطارية تخزين طاقة شمسية',
            'price' => 700,
            'stock' => 10,
            'image' => 'images/battery_200.jpg',
            'category_id' => $batteries->id
        ]);

        Product::create([
            'name' => 'بطارية جل 150Ah',
            'description' => 'بطارية جل للطاقة الشمسية',
            'price' => 350,
            'stock' => 15,
            'image' => 'images/battery_150.jpg',
            'category_id' => $batteries->id
        ]);


        // منتجات الانفيرترات
        Product::create([
            'name' => 'انفيرتر 5 كيلو',
            'description' => 'انفيرتر طاقة شمسية 5000 واط',
            'price' => 500,
            'stock' => 8,
            'image' => 'images/inverter_5kw.jpg',
            'category_id' => $inverters->id
        ]);

        Product::create([
            'name' => 'انفيرتر 10 كيلو',
            'description' => 'انفيرتر قوي للأنظمة الكبيرة',
            'price' => 900,
            'stock' => 5,
            'image' => 'images/inverter_10kw.jpg',
            'category_id' => $inverters->id
        ]);

    }
}
