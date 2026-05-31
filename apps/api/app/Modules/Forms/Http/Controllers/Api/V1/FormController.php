<?php

declare(strict_types=1);

namespace App\Modules\Forms\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Forms\Actions\CreateFormAction;
use App\Modules\Forms\Actions\DeleteFormAction;
use App\Modules\Forms\Actions\UpdateFormAction;
use App\Modules\Forms\Http\Requests\StoreFormRequest;
use App\Modules\Forms\Http\Requests\UpdateFormRequest;
use App\Modules\Forms\Http\Resources\FormResource;
use App\Modules\Forms\Http\Resources\FormSubmissionResource;
use App\Modules\Forms\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FormController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Form::class);

        $forms = Form::query()->with('fields')->orderBy('name')->get();

        return FormResource::collection($forms);
    }

    public function store(
        StoreFormRequest $request,
        CreateFormAction $action,
    ): JsonResponse {
        $this->authorize('create', Form::class);

        $form = $action->execute($request->validated());

        return (new FormResource($form))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Form $form): FormResource
    {
        $this->authorize('view', $form);

        return new FormResource($form->load('fields'));
    }

    public function update(
        UpdateFormRequest $request,
        Form $form,
        UpdateFormAction $action,
    ): FormResource {
        $this->authorize('update', $form);

        return new FormResource($action->execute($form, $request->validated()));
    }

    public function destroy(Form $form, DeleteFormAction $action): JsonResponse
    {
        $this->authorize('delete', $form);

        $action->execute($form);

        return response()->json(null, 204);
    }

    public function submissions(Form $form): AnonymousResourceCollection
    {
        $this->authorize('viewSubmissions', $form);

        $submissions = $form->submissions()->paginate(50);

        return FormSubmissionResource::collection($submissions);
    }
}
