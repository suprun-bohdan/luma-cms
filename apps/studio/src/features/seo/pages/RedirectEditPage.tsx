import { useNavigate, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { ApiError } from '../../../shared/api/client'
import { formatFieldErrors } from '../../../shared/utils/format'
import { RedirectForm } from '../components/RedirectForm'
import {
  useCreateRedirect,
  useDeleteRedirect,
  useRedirect,
  useUpdateRedirect,
} from '../hooks/useRedirects'
import { redirectFormToApiBody, redirectToFormValues } from '../schemas/redirect'

export function RedirectEditPage() {
  const navigate = useNavigate()
  const { id } = useParams()
  const isNew = !id || id === 'new'
  const redirectId = isNew ? undefined : Number(id)
  const redirectQuery = useRedirect(redirectId)
  const createMutation = useCreateRedirect()
  const updateMutation = useUpdateRedirect(redirectId ?? 0)
  const deleteMutation = useDeleteRedirect()

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
              { label: 'SEO', to: '/seo/redirects' },
              { label: isNew ? 'New redirect' : `Redirect #${id}` },
            ]}
          />
          <h1 className="mt-2 text-2xl font-semibold text-slate-900">
            {isNew ? 'Create redirect' : 'Edit redirect'}
          </h1>
          <p className="mt-1 text-sm text-slate-600">
            Public sitemap: <code className="font-mono">/sitemap.xml</code> · robots:{' '}
            <code className="font-mono">/robots.txt</code>
          </p>
        </div>

        {!isNew && redirectId !== undefined && (
          <Button
            variant="danger"
            disabled={deleteMutation.isPending}
            onClick={() =>
              deleteMutation.mutate(redirectId, {
                onSuccess: () => navigate('/seo/redirects'),
              })
            }
          >
            Delete
          </Button>
        )}
      </div>

      {!isNew && redirectQuery.isLoading && <LoadingState message="Loading redirect…" />}
      {!isNew && redirectQuery.isError && <ErrorAlert message={redirectQuery.error.message} />}

      {(isNew || redirectQuery.data) && (
        <Card>
          {errorMessage && (
            <div className="mb-4">
              <ErrorAlert message={errorMessage} />
            </div>
          )}

          <RedirectForm
            key={isNew ? 'new' : redirectQuery.data!.updated_at ?? redirectQuery.data!.id}
            initialValues={isNew ? undefined : redirectToFormValues(redirectQuery.data!)}
            submitLabel={isNew ? 'Create redirect' : 'Save changes'}
            loading={mutation.isPending}
            onSubmit={(values) => {
              const body = redirectFormToApiBody(values)

              if (isNew) {
                createMutation.mutate(body, {
                  onSuccess: (redirect) => navigate(`/seo/redirects/${redirect.id}/edit`),
                })
                return
              }

              updateMutation.mutate(body, {
                onSuccess: () => redirectQuery.refetch(),
              })
            }}
          />
        </Card>
      )}
    </>
  )
}
