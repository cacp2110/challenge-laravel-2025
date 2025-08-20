<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_lists_and_advances_an_order()
    {
        $payload = [
            'client_name' => 'Carlos Gómez',
            'items' => [
                ['description' => 'Lomo saltado', 'quantity' => 1, 'unit_price' => 60],
                ['description' => 'Inka Kola',     'quantity' => 2, 'unit_price' => 10],
            ],
        ];

        // Crear
        $res = $this->postJson('/api/orders', $payload)
            ->assertCreated()
            ->json();

        $orderId = $res['id'];

        // Listar (en cache)
        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonFragment(['id' => $orderId]);

        // Detalle con total
        $this->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('status', 'initiated')
            ->assertJsonPath('total', 80);

        // Avanzar -> sent
        $this->postJson("/api/orders/{$orderId}/advance")
            ->assertOk()
            ->assertJsonFragment(['message' => "Order advanced to 'sent'"]);

        // Avanzar -> delivered (y borrar)
        $this->postJson("/api/orders/{$orderId}/advance")
            ->assertOk()
            ->assertJsonFragment(['message' => 'Order delivered and removed']);

        // Ya no debería existir
        $this->getJson("/api/orders/{$orderId}")->assertNotFound();
    }
}
