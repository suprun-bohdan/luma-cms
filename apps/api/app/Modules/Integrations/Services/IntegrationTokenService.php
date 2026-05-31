<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Models\User;
use App\Modules\Integrations\Models\IntegrationToken;
use App\Modules\Plugins\Services\AuditLogService;
use Illuminate\Support\Str;

final class IntegrationTokenService
{
    public function __construct(
        private readonly IntegrationScopeCatalog $scopes,
        private readonly AuditLogService $auditLog,
    ) {
    }

    /**
     * @param  list<string>  $abilities
     * @return array{token: IntegrationToken, plain_text: string}
     */
    public function create(string $name, array $abilities, User $actor, ?\DateTimeInterface $expiresAt = null): array
    {
        $this->scopes->assertValidMany($abilities);

        $plainText = 'luma_'.Str::random(48);
        $hash = hash('sha256', $plainText);
        $prefix = substr($plainText, 0, 12);

        $token = IntegrationToken::query()->create([
            'name' => $name,
            'token_hash' => $hash,
            'token_prefix' => $prefix,
            'abilities' => array_values($abilities),
            'expires_at' => $expiresAt,
            'created_by' => $actor->id,
        ]);

        $this->auditLog->record(
            'integration.token.created',
            'integration_token',
            (string) $token->id,
            $actor,
            ['name' => $name, 'abilities' => $abilities],
        );

        return ['token' => $token, 'plain_text' => $plainText];
    }

    public function revoke(IntegrationToken $token, User $actor): void
    {
        $tokenId = (string) $token->id;
        $name = $token->name;

        $token->delete();

        $this->auditLog->record(
            'integration.token.revoked',
            'integration_token',
            $tokenId,
            $actor,
            ['name' => $name],
        );
    }

    public function findByPlainText(string $plainText): ?IntegrationToken
    {
        if ($plainText === '') {
            return null;
        }

        $token = IntegrationToken::query()
            ->where('token_hash', hash('sha256', $plainText))
            ->first();

        if ($token === null || $token->isExpired()) {
            return null;
        }

        $token->forceFill(['last_used_at' => now()])->save();

        return $token;
    }
}
