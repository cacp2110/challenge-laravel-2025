<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private OrderRepository $repo) {}

    public function listActive()
    {
        // TTL 30 segundos
        return Cache::remember('orders.active', 30, fn () => $this->repo->getActive());
    }

    public function create(array $payload): Order
    {
        $order = $this->repo->createWithItems($payload);
        Cache::forget('orders.active');
        return $order;
    }

    public function detail(int $id): Order
    {
        $order = $this->repo->findWithItems($id);
        abort_if(!$order, 404, 'Order not found');

        return $order;
    }

    public function advance(int $id): array
    {
        $order = $this->repo->findWithItems($id);
        abort_if(!$order, 404, 'Order not found');

        $next = match ($order->status) {
            'initiated' => 'sent',
            'sent'      => 'delivered',
            'delivered' => null,
            default     => null,
        };

        if (!$next) {
            throw ValidationException::withMessages([
                'status' => "Cannot advance order in status '{$order->status}'.",
            ]);
        }

        if ($next === 'delivered') {
            // al llegar a delivered: borrar de DB y del cache
            $this->repo->delete($order);
            Cache::forget('orders.active');

            return ['message' => 'Order delivered and removed'];
        }

        $this->repo->updateStatus($order, $next);
        Cache::forget('orders.active');

        return ['message' => "Order advanced to '{$next}'"];
    }
}
