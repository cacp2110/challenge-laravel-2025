<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *   title="OlaClick Orders API",
 *   version="1.0.0"
 * )
 * @OA\Server(
 *   url="http://localhost:8080",
 *   description="Local Docker"
 * )
 */

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    // GET /api/orders

    /**
     * @OA\Get(
     *   path="/api/orders",
     *   summary="Listar órdenes activas",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(): JsonResponse
    {
        $orders = $this->service->listActive();

        // Puedes devolver un resumen si prefieres:
        // ->map(fn($o) => [..., 'total' => $o->items->sum(fn($i)=>$i->quantity*$i->unit_price)])

        return response()->json($orders);
    }

    // POST /api/orders
        /**
     * @OA\Post(
     *   path="/api/orders",
     *   summary="Crear una orden",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"client_name","items"},
     *       @OA\Property(property="client_name", type="string", example="Carlos Gómez"),
     *       @OA\Property(
     *         property="items",
     *         type="array",
     *         @OA\Items(
     *           @OA\Property(property="description", type="string", example="Lomo saltado"),
     *           @OA\Property(property="quantity", type="integer", example=1),
     *           @OA\Property(property="unit_price", type="number", format="float", example=60)
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Creado")
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->service->create($request->validated());
        return response()->json($order, 201);
    }

    // GET /api/orders/{id}
    /**
     * @OA\Get(
     *   path="/api/orders/{id}",
     *   summary="Ver detalle de una orden",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->service->detail($id);

        // añade totales calculados
        $total = $order->items->sum(fn($i) => $i->quantity * $i->unit_price);
        $order->setAttribute('total', (float) number_format($total, 2, '.', ''));

        return response()->json($order);
    }

    // POST /api/orders/{id}/advance
    /**
     * @OA\Post(
     *   path="/api/orders/{id}/advance",
     *   summary="Avanzar estado de una orden",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=422, description="No se puede avanzar")
     * )
     */
    public function advance(int $id): JsonResponse
    {
        $result = $this->service->advance($id);
        return response()->json($result);
    }
}
