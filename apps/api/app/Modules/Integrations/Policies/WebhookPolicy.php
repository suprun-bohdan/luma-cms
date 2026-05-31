<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Policies;

use App\Models\User;
use App\Modules\Integrations\Models\IntegrationToken;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Models\WebhookDelivery;
use App\Modules\Users\Services\PermissionEvaluator;

final class WebhookPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.manage')
            || $this->permissions->hasPermission($user, 'integrations.deliveries.read');
    }

    public function view(?User $user, Webhook $webhook): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.manage');
    }

    public function update(?User $user, Webhook $webhook): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.manage');
    }

    public function delete(?User $user, Webhook $webhook): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.manage');
    }

    public function viewDeliveries(?User $user, Webhook $webhook): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.manage')
            || $this->permissions->hasPermission($user, 'integrations.deliveries.read');
    }

    public function retryDelivery(?User $user, Webhook $webhook, WebhookDelivery $delivery): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.manage');
    }
}
