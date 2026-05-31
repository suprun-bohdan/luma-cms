import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { ConfirmDialog } from '../../../shared/components/ConfirmDialog'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { ApiError } from '../../../shared/api/client'
import { CollectionTable } from '../components/CollectionTable'
import { useCollections, useDeleteCollection } from '../hooks/useCollections'

export function CollectionsListPage() {
  const collectionsQuery = useCollections()
  const deleteMutation = useDeleteCollection()
  const [pendingDeleteSlug, setPendingDeleteSlug] = useState<string | null>(null)

  return (
    <>
      <PageHeader
        title="Collections"
        description="Content types for structured entries."
        actions={
          <Link to="/collections/new">
            <Button>Create collection</Button>
          </Link>
        }
      />

      {collectionsQuery.isLoading && <LoadingState message="Loading collections…" />}
      {collectionsQuery.isError && (
        <ErrorAlert
          message={
            collectionsQuery.error instanceof Error
              ? collectionsQuery.error.message
              : 'Failed to load collections'
          }
        />
      )}

      {collectionsQuery.data?.length === 0 && (
        <EmptyState
          title="No collections yet"
          description="Create your first collection to define a content type."
          action={
            <Link to="/collections/new">
              <Button>Create collection</Button>
            </Link>
          }
        />
      )}

      {collectionsQuery.data && collectionsQuery.data.length > 0 && (
        <CollectionTable
          collections={collectionsQuery.data}
          onDelete={(slug) => setPendingDeleteSlug(slug)}
        />
      )}

      <ConfirmDialog
        open={pendingDeleteSlug !== null}
        title="Delete collection"
        description="This will permanently delete the collection and related fields and entries."
        confirmLabel="Delete"
        loading={deleteMutation.isPending}
        onCancel={() => setPendingDeleteSlug(null)}
        onConfirm={() => {
          if (!pendingDeleteSlug) {
            return
          }

          deleteMutation.mutate(pendingDeleteSlug, {
            onSuccess: () => setPendingDeleteSlug(null),
            onError: (error) => {
              if (error instanceof ApiError && error.status === 403) {
                setPendingDeleteSlug(null)
              }
            },
          })
        }}
      />
    </>
  )
}
