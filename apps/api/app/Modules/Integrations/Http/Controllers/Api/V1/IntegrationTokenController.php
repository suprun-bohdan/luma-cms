<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Http\Requests\StoreIntegrationTokenRequest;
use App\Modules\Integrations\Http\Resources\IntegrationTokenResource;
use App\Modules\Integrations\Models\IntegrationToken;
use App\Modules\Integrations\Services\IntegrationScopeCatalog;
use App\Modules\Integrations\Services\IntegrationTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class IntegrationTokenController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', IntegrationToken::class);

        $tokens = IntegrationToken::query()->orderByDesc('created_at')->get();

        return IntegrationTokenResource::collection($tokens);
    }

    public function store(
        StoreIntegrationTokenRequest $request,
        IntegrationTokenService $service,
    ): JsonResponse {
        $this->authorize('create', IntegrationToken::class);

        $expiresAt = $request->validated('expires_at');
        $result = $service->create(
            $request->validated('name'),
            $request->validated('abilities'),
            $request->user(),
            $expiresAt !== null ? new \DateTimeImmutable($expiresAt) : null,
        );

        return response()->json([
            'data' => array_merge(
                (new IntegrationTokenResource($result['token']))->resolve(),
                ['plain_text_token' => $result['plain_text']],
            ),
        ], 201);
    }

    public function destroy(IntegrationToken $integrationToken, IntegrationTokenService $service): JsonResponse
    {
        $this->authorize('delete', $integrationToken);

        $service->revoke($integrationToken, request()->user());

        return response()->json(null, 204);
    }

    public function scopes(IntegrationScopeCatalog $scopes): JsonResponse
    {
        $this->authorize('viewAny', IntegrationToken::class);

        return response()->json([
            'data' => $scopes->all(),
        ]);
    }
}
