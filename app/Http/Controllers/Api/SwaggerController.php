<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Uptime Monitor API',
    description: 'REST API сервис мониторинга доступности сайтов',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
#[OA\Server(
    url: 'http://localhost:8000/api/v1',
    description: 'Local server',
)]
#[OA\PathItem(path: '/')]
final class SwaggerController {}
