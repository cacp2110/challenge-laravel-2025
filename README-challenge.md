# OlaClick Backend Challenge – Laravel 10 + PostgreSQL + Redis + Docker

API REST de órdenes. Cache con Redis, PostgreSQL, contenedores Docker, principios **SOLID** (Controller → Service → Repository), **Form Requests** y **tests**.

---

## Endpoints
- `GET /api/orders` — lista órdenes **activas** (`status != delivered`) con **cache Redis (TTL 30s)**.
- `POST /api/orders` — crea orden (estado inicial `initiated`).
- `GET /api/orders/{id}` — detalle de la orden **incluye items y total**.
- `POST /api/orders/{id}/advance` — transición `initiated → sent → delivered`.  
  Al llegar a `delivered` se **elimina** de la base de datos **y se invalida la caché**.

---

## Levantar con Docker

```bash
# 1) Arrancar contenedores
docker compose up -d --build

# 2) Configurar entorno dentro del contenedor
docker compose exec app cp .env.docker .env
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 3) Migraciones (usa --seed si agregas seeders)
docker compose exec app php artisan migrate

# 4) Permisos para logs y cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache

### URL del servicio
- Nginx sirve en: http://localhost:8080

### Servicios
- **app** (PHP-FPM)
- **web** (nginx)
- **db** (Postgres)
- **redis**

```

---

### Probar rápido (curl)

```bash
# Crear orden
curl -X POST http://localhost:8080/api/orders \
  -H 'Content-Type: application/json' \
  -d '{
    "client_name":"Carlos Gómez",
    "items":[
      {"description":"Lomo saltado","quantity":1,"unit_price":60},
      {"description":"Inka Kola","quantity":2,"unit_price":10}
    ]
  }'

# Listar activas (usa Redis con TTL 30s)
curl http://localhost:8080/api/orders

# Ver detalle (incluye total calculado)
curl http://localhost:8080/api/orders/1

# Avanzar estado (initiated -> sent)
curl -X POST http://localhost:8080/api/orders/1/advance

# Avanzar a delivered (se borra de DB y cache)
curl -X POST http://localhost:8080/api/orders/1/advance
---
```
### Test
```bash
docker compose exec app php artisan test

```

### Arquitectura (SOLID)
```bash
-app/Http/Controllers/OrderController.php — capa HTTP.

-app/Http/Requests/StoreOrderRequest.php — validación (Form Request).

-app/Services/OrderService.php — reglas de negocio, cache (Redis), transición de estados.

-app/Repositories/OrderRepository.php — acceso a datos (Eloquent + transacciones).

-app/Models/Order.php, app/Models/OrderItem.php — modelos y relaciones.

routes/api.php — rutas REST.
```
---
## Estructura del repo(resumen)
```bash
app/
  Http/
    Controllers/OrderController.php
    Requests/StoreOrderRequest.php
  Models/{Order,OrderItem}.php
  Repositories/OrderRepository.php
  Services/OrderService.php
routes/
  api.php
database/
  migrations/
  seeders/
Dockerfile
docker-compose.yml
nginx.conf
.env.docker
README.md

```
---
## Respuestas (breves) a las preguntas opcionales
```bash

1- Escalabilidad con alta concurrencia

Cache de lecturas calientes (Redis, TTL corto, invalidación selectiva).

Bloqueo transaccional en advance (o optimistic locking).

Paginación y respuestas lean; índices correctos.

Pool de conexiones DB y timeouts sensatos.

Autoscaling del PHP-FPM/web; CDN/NGINX para estáticos.

Observabilidad (logs/metrics/traces) + alertas.

2- Desacoplar dominio de Laravel/Eloquent

Arquitectura Hexagonal (Ports & Adapters):

Domain (Entidades, VOs, Servicios de dominio).

Application (casos de uso).

Infra (Eloquent como adapter de OrderRepositoryInterface).

Usar DTOs/Mappers para salir de Eloquent en el límite.

Domain Events y servicios puros sin depender del framework.

3- Versionado en producción

URI versioning (/api/v1/..., /api/v2/...) con route groups separados.

Mantener contrato estable; deprecations con fechas.

Tests por versión; changelogs y semver a nivel de API.

```