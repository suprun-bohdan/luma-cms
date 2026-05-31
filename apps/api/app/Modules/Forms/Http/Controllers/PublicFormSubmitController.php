<?php

declare(strict_types=1);

namespace App\Modules\Forms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forms\Actions\SubmitFormAction;
use App\Modules\Forms\Models\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class PublicFormSubmitController extends Controller
{
    public function __invoke(
        Request $request,
        Form $form,
        SubmitFormAction $action,
    ): RedirectResponse {
        try {
            $submission = $action->execute(
                $form,
                $request->all(),
                $request->ip(),
                $request->userAgent(),
            );
        } catch (ValidationException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        if ($submission === null) {
            return redirect()
                ->back()
                ->with('form_success', 'Thank you! Your message has been sent.');
        }

        return redirect()
            ->back()
            ->with('form_success', 'Thank you! Your message has been sent.');
    }
}
