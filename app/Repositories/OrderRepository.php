<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function getActive()
    {
        return Order::with('items')
            ->where('status', '!=', 'delivered')
            ->orderByDesc('id')
            ->get();
    }

    public function createWithItems(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            /** @var Order $order */
            $order = Order::create([
                'client_name' => $data['client_name'],
                'status'      => 'initiated',
            ]);

            $items = collect($data['items'])
                ->map(fn($i) => [
                    'description' => $i['description'],
                    'quantity'    => (int)$i['quantity'],
                    'unit_price'  => (float)$i['unit_price'],
                ])->all();

            $order->items()->createMany($items);

            return $order->load('items');
        });
    }

    public function findWithItems(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function updateStatus(Order $order, string $to): Order
    {
        $order->update(['status' => $to]);
        return $order->fresh('items');
    }

    public function delete(Order $order): void
    {
        $order->delete(); // cascade borra items
    }
}
