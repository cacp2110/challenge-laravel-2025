<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Orden 1
        $o1 = Order::create([
            'client_name' => 'Demo Cliente 1',
            'status'      => 'initiated',
        ]);
        $o1->items()->createMany([
            ['description' => 'Lomo saltado', 'quantity' => 1, 'unit_price' => 55],
            ['description' => 'Chicha morada', 'quantity' => 2, 'unit_price' => 8],
        ]);

        // Orden 2
        $o2 = Order::create([
            'client_name' => 'Demo Cliente 2',
            'status'      => 'sent',
        ]);
        $o2->items()->createMany([
            ['description' => 'Ceviche', 'quantity' => 1, 'unit_price' => 48],
            ['description' => 'Inka Kola', 'quantity' => 1, 'unit_price' => 10],
        ]);

        // Orden 3
        $o3 = Order::create([
            'client_name' => 'Demo Cliente 3',
            'status'      => 'initiated',
        ]);
        $o3->items()->createMany([
            ['description' => 'Aji de gallina', 'quantity' => 1, 'unit_price' => 42],
        ]);
    }
}
