<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\MonitorDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\MonitorRequest;
use App\Http\Resources\MonitorResource;
use App\Repositories\MonitorRepository;
use App\Services\MonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MonitorController extends Controller
{
    public function __construct(
        private readonly MonitorService $service,
        private readonly MonitorRepository $repository,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $monitors = $this->service->getAllForUser(
            $request->user()->id,
            (int) $request->query('per_page', 15)
        );

        return MonitorResource::collection($monitors);
    }

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

    public function show(Request $request, int $id): JsonResponse
    {
        $monitor = $this->repository->findForUser($id, $request->user()->id);

        if (! $monitor) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        return (new MonitorResource($monitor))->response();
    }

    public function update(MonitorRequest $request, int $id): JsonResponse
    {
        $monitor = $this->repository->findForUser($id, $request->user()->id);

        if (! $monitor) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        $monitor = $this->service->update($monitor, MonitorDTO::fromRequest($request));

        return (new MonitorResource($monitor))->response();
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $monitor = $this->repository->findForUser($id, $request->user()->id);

        if (! $monitor) {
            return response()->json(['message' => 'Monitor not found'], 404);
        }

        $this->service->delete($monitor);

        return response()->json(['message' => 'Monitor deleted'], 200);
    }
}
