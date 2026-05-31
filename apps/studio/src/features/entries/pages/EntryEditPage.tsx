import { Link, useNavigate, useParams } from 'react-router-dom'
import { useMemo } from 'react'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { ApiError } from '../../../shared/api/client'
import { canDeleteContent, formatFieldErrors } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import { useCollections } from '../../collections/hooks/useCollections'
import { EntryForm } from '../components/EntryForm'
import { EntryStatusBadge } from '../components/EntryStatusBadge'
import {
  useCreateEntry,
  useDeleteEntry,
  useEntry,
  usePublishEntry,
  useUnpublishEntry,
  useUpdateEntry,
} from '../hooks/useEntries'

export function EntryEditPage() {
  const navigate = useNavigate()
  const { slug, id } = useParams()
  const isNew = !id
  const entryId = id ? Number(id) : undefined
  const entryQuery = useEntry(isNew ? undefined : entryId)
  const collectionsQuery = useCollections()
  const resolvedSlug = useMemo(() => {
    if (slug) {
      return slug
    }

    if (!entryQuery.data || !collectionsQuery.data) {
      return ''
    }

    return (
      collectionsQuery.data.find(
        (collection) => collection.id === entryQuery.data!.collection_id,
      )?.slug ?? ''
    )
  }, [slug, entryQuery.data, collectionsQuery.data])

  const { user } = useAuth()
  const createMutation = useCreateEntry(resolvedSlug)
  const updateMutation = useUpdateEntry(entryId ?? 0, resolvedSlug)
  const publishMutation = usePublishEntry(resolvedSlug)
  const unpublishMutation = useUnpublishEntry(resolvedSlug)
  const deleteMutation = useDeleteEntry(resolvedSlug)
  const canDelete = canDeleteContent(user)

  const mutation = isNew ? createMutation : updateMutation
  const errorMessage =
    mutation.error instanceof ApiError
      ? formatFieldErrors(mutation.error.errors) || mutation.error.message
      : mutation.error instanceof Error
        ? mutation.error.message
        : null

  const isLoadingContext =
    (!isNew && entryQuery.isLoading) ||
    (!isNew && collectionsQuery.isLoading) ||
    (isNew && !resolvedSlug)

  return (
    <>
      <PageHeader
        title={isNew ? 'Create entry' : `Edit entry #${entryId}`}
        breadcrumbs={
          <Breadcrumbs
            items={[
              { label: 'Collections', to: '/collections' },
              ...(resolvedSlug
                ? [
                    { label: resolvedSlug, to: `/collections/${resolvedSlug}/edit` },
                    { label: 'Entries', to: `/collections/${resolvedSlug}/entries` },
                  ]
                : []),
              { label: isNew ? 'New' : `#${entryId}` },
            ]}
          />
        }
        actions={
          !isNew && entryId ? (
            <div className="flex flex-wrap gap-2">
              <Link to={`/entries/${entryId}/preview`}>
                <Button variant="secondary">Preview</Button>
              </Link>
              {entryQuery.data?.status !== 'published' && (
                <Button
                  disabled={publishMutation.isPending}
                  onClick={() =>
                    publishMutation.mutate(entryId, {
                      onSuccess: () => entryQuery.refetch(),
                    })
                  }
                >
                  Publish
                </Button>
              )}
              {entryQuery.data?.status === 'published' && (
                <Button
                  variant="secondary"
                  disabled={unpublishMutation.isPending}
                  onClick={() =>
                    unpublishMutation.mutate(entryId, {
                      onSuccess: () => entryQuery.refetch(),
                    })
                  }
                >
                  Unpublish
                </Button>
              )}
              {canDelete && (
                <Button
                  variant="danger"
                  disabled={deleteMutation.isPending}
                  onClick={() =>
                    deleteMutation.mutate(entryId, {
                      onSuccess: () => {
                        if (resolvedSlug) {
                          navigate(`/collections/${resolvedSlug}/entries`)
                        } else {
                          navigate('/collections')
                        }
                      },
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

      {isLoadingContext && <LoadingState />}
      {!isNew && entryQuery.isError && <ErrorAlert message={entryQuery.error.message} />}
      {isNew && !resolvedSlug && !collectionsQuery.isLoading && (
        <ErrorAlert message="Collection slug is required to create an entry." />
      )}

      {!isNew && entryQuery.data && (
        <div className="mb-4">
          <EntryStatusBadge status={entryQuery.data.status} />
        </div>
      )}

      {!isLoadingContext && resolvedSlug && (isNew || entryQuery.data) && (
        <Card>
          {errorMessage && (
            <div className="mb-4">
              <ErrorAlert message={errorMessage} />
            </div>
          )}
          <EntryForm
            key={isNew ? 'new' : entryQuery.data!.updated_at ?? String(entryId)}
            collectionSlug={resolvedSlug}
            initialValues={isNew ? undefined : { data: entryQuery.data!.data }}
            submitLabel={isNew ? 'Save draft' : 'Save changes'}
            loading={mutation.isPending}
            onSubmit={(values) => {
              if (isNew) {
                createMutation.mutate(values, {
                  onSuccess: (entry) => navigate(`/entries/${entry.id}/edit`),
                })
                return
              }

              updateMutation.mutate(values, {
                onSuccess: () => entryQuery.refetch(),
              })
            }}
          />
        </Card>
      )}
    </>
  )
}
