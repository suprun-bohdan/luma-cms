<?php

declare(strict_types=1);

namespace App\Modules\Forms\Actions;

use App\Modules\Forms\Models\Form;

final class UpdateFormAction
{
    public function __construct(
        private readonly SyncFormFieldsAction $syncFields,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Form $form, array $data): Form
    {
        $fields = $data['fields'] ?? null;
        unset($data['fields']);

        $form->fill($data);
        $form->save();
        $this->syncFields->execute($form, $fields);

        return $form->load('fields');
    }
}
