<?php

declare(strict_types=1);

namespace App\Modules\Forms\Actions;

use App\Modules\Forms\Models\Form;

final class DeleteFormAction
{
    public function execute(Form $form): void
    {
        $form->delete();
    }
}
