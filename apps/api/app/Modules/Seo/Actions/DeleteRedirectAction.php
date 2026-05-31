<?php

declare(strict_types=1);

namespace App\Modules\Seo\Actions;

use App\Modules\Seo\Models\Redirect;

final class DeleteRedirectAction
{
    public function execute(Redirect $redirect): void
    {
        $redirect->delete();
    }
}
