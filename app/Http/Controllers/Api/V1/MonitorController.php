<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\MonitorDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\MonitorRequest;
use App\Http\Resources\MonitorLogResource;
use App\Http\Resources\MonitorResource;
use App\Repositories\MonitorLogRepository;
use App\Repositories\MonitorRepository;
use App\Services\MonitorService;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

final class MonitorController extends Controller
{
    public function __construct(
        private readonly MonitorService $service,
        private readonly StatsService $statsService,
        private readonly MonitorRepository $repository,
        private readonly MonitorLogRepository $logRepository,
    ) {}

    #[OA\Get(
        path: '/api/v1/monitors',
        summary: 'Список мониторов',
        tags: ['Monitors'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Список мониторов'),
            new OA\Response(response: 401, description: 'Не авторизован'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $monitors = $this->service->getAllForUser(
            $request->user()->id,
            (int) $request->query('per_page', 15)
        );

        return MonitorResource::collection($monitors);
    }

    #[OA\Post(
        path: '/api/v1/monitors',
        summary: 'Создать монитор',
        tags: ['Monitors'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'url', 'interval'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Google'),
                    new OA\Property(property: 'url', type: 'string', example: 'https://google.com'),
                    new OA\Property(property: 'interval', type: 'integer', enum: [1, 5, 10, 30], example: 5),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Монитор создан'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(MonitorRequest $request): JsonResponse
    {
        $monitor = $this->service->create(
            $request->user()->id,
            MonitorDTO::fromRequest($request)
        );

        return (new MonitorResource($monitor))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/monitors/{id}',
        summary: 'Получить монитор',
        tags: ['Monitors'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Монитор'),
            new OA\Response(response: 404, description: 'Не найден'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $monitor = $this->repository->findForUser($id, $request->user()->id);

        if (! $monitor) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        return (new MonitorResource($monitor))->response();
    }

    #[OA\Put(
        path: '/api/v1/monitors/{id}',
        summary: 'Обновить монитор',
        tags: ['Monitors'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'url', type: 'string'),
                    new OA\Property(property: 'interval', type: 'integer', enum: [1, 5, 10, 30]),
                    new OA\Property(property: 'is_active', type: 'boolean'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Монитор обновлён'),
            new OA\Response(response: 404, description: 'Не найден'),
        ]
    )]
    public function update(MonitorRequest $request, int $id): JsonResponse
    {
        $monitor = $this->repository->findForUser($id, $request->user()->id);

        if (! $monitor) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        $monitor = $this->service->update($monitor, MonitorDTO::fromRequest($request));

        return (new MonitorResource($monitor))->response();
    }

    #[OA\Delete(
        path: '/api/v1/monitors/{id}',
        summary: 'Удалить монитор',
        tags: ['Monitors'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Монитор удалён'),
            new OA\Response(response: 404, description: 'Не найден'),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $monitor = $this->repository->findForUser($id, $request->user()->id);

        if (! $monitor) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        $this->service->delete($monitor);

        return response()->json(['message' => 'Monitor deleted'], 200);
    }

    #[OA\Get(
        path: '/api/v1/monitors/{id}/history',
        summary: 'История проверок монитора',
        tags: ['Monitors'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'История проверок'),
            new OA\Response(response: 404, description: 'Не найден'),
        ]
    )]
    public function history(Request $request, int $monitor): JsonResponse
    {
        $monitorModel = $this->repository->findForUser($monitor, $request->user()->id);

        if (! $monitorModel) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        $logs = $this->logRepository->getPaginatedForMonitor(
            $monitorModel,
            (int) $request->query('per_page', 20),
        );

        return response()->json(
            MonitorLogResource::collection($logs)->response()->getData(true)
        );
    }

    #[OA\Get(
        path: '/api/v1/monitors/{id}/stats',
        summary: 'Статистика монитора',
        tags: ['Monitors'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Статистика'),
            new OA\Response(response: 404, description: 'Не найден'),
        ]
    )]
    public function stats(Request $request, int $monitor): JsonResponse
    {
        $monitorModel = $this->repository->findForUser($monitor, $request->user()->id);

        if (! $monitorModel) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        return response()->json([
            'data' => $this->statsService->getStats($monitorModel),
        ]);
    }
}
