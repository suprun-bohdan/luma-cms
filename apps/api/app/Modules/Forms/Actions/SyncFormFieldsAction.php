<?php

declare(strict_types=1);

namespace App\Modules\Forms\Actions;

use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Models\FormField;

final class SyncFormFieldsAction
{
    /**
     * @param  array<int, array<string, mixed>>|null  $fields
     */
    public function execute(Form $form, ?array $fields): void
    {
        if ($fields === null) {
            return;
        }

        $form->fields()->delete();

        foreach ($fields as $index => $field) {
            FormField::query()->create([
                'form_id' => $form->id,
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => $field['required'] ?? false,
                'sort_order' => $field['sort_order'] ?? $index,
            ]);
        }
    }
}
