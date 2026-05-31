import { useNavigate, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { ApiError } from '../../../shared/api/client'
import { formatFieldErrors } from '../../../shared/utils/format'
import { FormForm } from '../components/FormForm'
import {
  useCreateForm,
  useDeleteForm,
  useForm,
  useUpdateForm,
} from '../hooks/useForms'
import { formFormToApiBody, formToFormValues } from '../schemas/form'

export function FormEditPage() {
  const navigate = useNavigate()
  const { slug } = useParams()
  const isNew = !slug || slug === 'new'
  const formSlug = isNew ? undefined : slug
  const formQuery = useForm(formSlug)
  const createMutation = useCreateForm()
  const updateMutation = useUpdateForm(formSlug ?? '')
  const deleteMutation = useDeleteForm()

  const mutation = isNew ? createMutation : updateMutation
  const errorMessage =
    mutation.error instanceof ApiError
      ? formatFieldErrors(mutation.error.errors) || mutation.error.message
      : mutation.error instanceof Error
        ? mutation.error.message
        : null

  return (
    <>
      <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
          <Breadcrumbs
            items={[
              { label: 'Forms', to: '/forms' },
              { label: isNew ? 'New form' : formSlug ?? 'Form' },
            ]}
          />
          <h1 className="mt-2 text-2xl font-semibold text-slate-900">
            {isNew ? 'Create form' : 'Edit form'}
          </h1>
          <p className="mt-1 text-sm text-slate-600">
            Embed on pages with a <code className="font-mono">contact_form</code> block using{' '}
            <code className="font-mono">form_slug</code>.
          </p>
        </div>

        {!isNew && formSlug && (
          <div className="flex gap-2">
            <Button variant="secondary" onClick={() => navigate(`/forms/${formSlug}/submissions`)}>
              View submissions
            </Button>
            <Button
              variant="danger"
              disabled={deleteMutation.isPending}
              onClick={() =>
                deleteMutation.mutate(formSlug, {
                  onSuccess: () => navigate('/forms'),
                })
              }
            >
              Delete
            </Button>
          </div>
        )}
      </div>

      {!isNew && formQuery.isLoading && <LoadingState message="Loading form…" />}
      {!isNew && formQuery.isError && <ErrorAlert message={formQuery.error.message} />}

      {(isNew || formQuery.data) && (
        <Card>
          {errorMessage && (
            <div className="mb-4">
              <ErrorAlert message={errorMessage} />
            </div>
          )}

          <FormForm
            key={isNew ? 'new' : formQuery.data!.updated_at ?? formQuery.data!.slug}
            initialValues={isNew ? undefined : formToFormValues(formQuery.data!)}
            submitLabel={isNew ? 'Create form' : 'Save changes'}
            loading={mutation.isPending}
            onSubmit={(values) => {
              const body = formFormToApiBody(values)

              if (isNew) {
                createMutation.mutate(body, {
                  onSuccess: (form) => navigate(`/forms/${form.slug}/edit`),
                })
                return
              }

              updateMutation.mutate(body, {
                onSuccess: () => formQuery.refetch(),
              })
            }}
          />
        </Card>
      )}
    </>
  )
}
