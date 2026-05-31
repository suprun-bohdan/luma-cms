<?php

declare(strict_types=1);

namespace App\Modules\Forms\Database\Seeders;

use App\Modules\Forms\Enums\FormFieldType;
use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Models\FormField;
use Illuminate\Database\Seeder;

class ContactFormSeeder extends Seeder
{
    public function run(): void
    {
        $form = Form::query()->updateOrCreate(
            ['slug' => 'contact'],
            [
                'name' => 'Contact',
                'description' => 'Default contact form for business websites.',
                'is_active' => true,
            ],
        );

        FormField::query()->where('form_id', $form->id)->delete();

        $fields = [
            ['name' => 'name', 'label' => 'Name', 'type' => FormFieldType::Text, 'required' => true],
            ['name' => 'email', 'label' => 'Email', 'type' => FormFieldType::Email, 'required' => true],
            ['name' => 'message', 'label' => 'Message', 'type' => FormFieldType::Textarea, 'required' => true],
        ];

        foreach ($fields as $index => $field) {
            FormField::query()->create([
                'form_id' => $form->id,
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => $field['required'],
                'sort_order' => $index,
            ]);
        }
    }
}
