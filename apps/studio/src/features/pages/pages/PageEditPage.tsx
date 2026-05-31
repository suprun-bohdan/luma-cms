import { useNavigate, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { ApiError } from '../../../shared/api/client'
import { canDeleteContent, formatFieldErrors } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import { PageForm } from '../components/PageForm'
import { PageStatusBadge } from '../components/PageStatusBadge'
import {
  useCreatePage,
  useDeletePage,
  usePage,
  usePublishPage,
  useUnpublishPage,
  useUpdatePage,
} from '../hooks/usePages'
import { pageFormToApiBody, pageToFormValues } from '../schemas/page'

export function PageEditPage() {
  const navigate = useNavigate()
  const { slug } = useParams()
  const isNew = !slug || slug === 'new'
  const pageQuery = usePage(isNew ? undefined : slug)
  const createMutation = useCreatePage()
  const updateMutation = useUpdatePage(slug ?? '')
  const publishMutation = usePublishPage()
  const unpublishMutation = useUnpublishPage()
  const deleteMutation = useDeletePage()
  const { user } = useAuth()
  const canDelete = canDeleteContent(user)

  const mutation = isNew ? createMutation : updateMutation
  const errorMessage =
    mutation.error instanceof ApiError
      ? formatFieldErrors(mutation.error.errors) || mutation.error.message
      : mutation.error instanceof Error
        ? mutation.error.message
        : null

  function submitValues(values: Parameters<typeof pageFormToApiBody>[0]) {
    try {
      return pageFormToApiBody(values)
    } catch (error) {
      throw error instanceof Error ? error : new Error('Invalid page content')
    }
  }

  return (
    <>
      <PageHeader
        title={isNew ? 'Create page' : `Edit page`}
        breadcrumbs={
          <Breadcrumbs
            items={[
              { label: 'Pages', to: '/pages' },
              { label: isNew ? 'New' : slug ?? 'Edit' },
            ]}
          />
        }
        actions={
          !isNew && slug ? (
            <div className="flex flex-wrap gap-2">
              {pageQuery.data?.status !== 'published' && (
                <Button
                  disabled={publishMutation.isPending}
                  onClick={() =>
                    publishMutation.mutate(slug, {
                      onSuccess: () => pageQuery.refetch(),
                    })
                  }
                >
                  Publish
                </Button>
              )}
              {pageQuery.data?.status === 'published' && (
                <>
                  <Button
                    variant="secondary"
                    disabled={unpublishMutation.isPending}
                    onClick={() =>
                      unpublishMutation.mutate(slug, {
                        onSuccess: () => pageQuery.refetch(),
                      })
                    }
                  >
                    Unpublish
                  </Button>
                  <a href={`/p/${slug}`} target="_blank" rel="noreferrer">
                    <Button variant="secondary">View public</Button>
                  </a>
                </>
              )}
              {canDelete && (
                <Button
                  variant="danger"
                  disabled={deleteMutation.isPending}
                  onClick={() =>
                    deleteMutation.mutate(slug, {
                      onSuccess: () => navigate('/pages'),
                    })
                  }
                >
                  Delete
                </Button>
              )}
            </div>
          ) : undefined
        }
      />

      {!isNew && pageQuery.isLoading && <LoadingState />}
      {!isNew && pageQuery.isError && <ErrorAlert message={pageQuery.error.message} />}

      {!isNew && pageQuery.data && (
        <div className="mb-4">
          <PageStatusBadge status={pageQuery.data.status} />
        </div>
      )}

      {(isNew || pageQuery.data) && (
        <Card>
          {errorMessage && (
            <div className="mb-4">
              <ErrorAlert message={errorMessage} />
            </div>
          )}
          <PageForm
            key={isNew ? 'new' : pageQuery.data!.updated_at ?? pageQuery.data!.slug}
            initialValues={isNew ? undefined : pageToFormValues(pageQuery.data!)}
            previewEnabled={!isNew}
            submitLabel={isNew ? 'Save draft' : 'Save changes'}
            loading={mutation.isPending}
            onSubmit={(values) => {
              let body: ReturnType<typeof pageFormToApiBody>
              try {
                body = submitValues(values)
              } catch {
                return
              }

              if (isNew) {
                createMutation.mutate(body, {
                  onSuccess: (page) => navigate(`/pages/${page.slug}/edit`),
                })
                return
              }

              updateMutation.mutate(body, {
                onSuccess: (page) => {
                  if (page.slug !== slug) {
                    navigate(`/pages/${page.slug}/edit`, { replace: true })
                  } else {
                    pageQuery.refetch()
                  }
                },
              })
            }}
          />
        </Card>
      )}
    </>
  )
}
