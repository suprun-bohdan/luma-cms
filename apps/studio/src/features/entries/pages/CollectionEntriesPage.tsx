import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { ConfirmDialog } from '../../../shared/components/ConfirmDialog'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { ApiError } from '../../../shared/api/client'
import { canDeleteContent, formatDate } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import { useCollection } from '../../collections/hooks/useCollections'
import { EntryStatusBadge } from '../components/EntryStatusBadge'
import {
  useDeleteEntry,
  useEntries,
  usePublishEntry,
  useUnpublishEntry,
} from '../hooks/useEntries'
import type { EntryStatus } from '../schemas/entry'

const filters: Array<{ label: string; value?: EntryStatus }> = [
  { label: 'All' },
  { label: 'Draft', value: 'draft' },
  { label: 'Published', value: 'published' },
  { label: 'Archived', value: 'archived' },
]

export function CollectionEntriesPage() {
  const { slug = '' } = useParams()
  const { user } = useAuth()
  const [statusFilter, setStatusFilter] = useState<EntryStatus | undefined>()
  const collectionQuery = useCollection(slug)
  const entriesQuery = useEntries(slug, statusFilter)
  const publishMutation = usePublishEntry(slug)
  const unpublishMutation = useUnpublishEntry(slug)
  const deleteMutation = useDeleteEntry(slug)
  const [pendingDeleteId, setPendingDeleteId] = useState<number | null>(null)
  const canDelete = canDeleteContent(user)

  return (
    <>
      <PageHeader
        title="Entries"
        description={collectionQuery.data?.name ?? 'Collection entries'}
        breadcrumbs={
          <Breadcrumbs
            items={[
              { label: 'Collections', to: '/collections' },
              { label: slug, to: `/collections/${slug}/edit` },
              { label: 'Entries' },
            ]}
          />
        }
        actions={
          <div className="flex gap-2">
            <Link to={`/collections/${slug}/fields`}>
              <Button variant="secondary">Fields</Button>
            </Link>
            <Link to={`/collections/${slug}/entries/new`}>
              <Button>Create entry</Button>
            </Link>
          </div>
        }
      />

      <div className="mb-4 flex flex-wrap gap-2">
        {filters.map((filter) => (
          <Button
            key={filter.label}
            variant={statusFilter === filter.value ? 'primary' : 'secondary'}
            onClick={() => setStatusFilter(filter.value)}
          >
            {filter.label}
          </Button>
        ))}
      </div>

      {entriesQuery.isLoading && <LoadingState message="Loading entries…" />}
      {entriesQuery.isError && <ErrorAlert message={entriesQuery.error.message} />}

      {entriesQuery.data?.length === 0 && (
        <EmptyState
          title="No entries found"
          description="Create an entry or change the status filter."
          action={
            <Link to={`/collections/${slug}/entries/new`}>
              <Button>Create entry</Button>
            </Link>
          }
        />
      )}

      {entriesQuery.data && entriesQuery.data.length > 0 && (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
          <table className="min-w-full text-left text-sm">
            <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
              <tr>
                <th className="px-4 py-3 font-medium">ID</th>
                <th className="px-4 py-3 font-medium">Status</th>
                <th className="px-4 py-3 font-medium">Updated</th>
                <th className="px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              {entriesQuery.data.map((entry) => (
                <tr key={entry.id} className="border-b border-slate-100 last:border-b-0">
                  <td className="px-4 py-3 font-medium">#{entry.id}</td>
                  <td className="px-4 py-3">
                    <EntryStatusBadge status={entry.status} />
                  </td>
                  <td className="px-4 py-3">{formatDate(entry.updated_at)}</td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-2">
                      <Link to={`/entries/${entry.id}/edit`}>
                        <Button variant="secondary">Edit</Button>
                      </Link>
                      <Link to={`/entries/${entry.id}/preview`}>
                        <Button variant="ghost">Preview</Button>
                      </Link>
                      {entry.status !== 'published' && (
                        <Button
                          variant="secondary"
                          disabled={publishMutation.isPending}
                          onClick={() => publishMutation.mutate(entry.id)}
                        >
                          Publish
                        </Button>
                      )}
                      {entry.status === 'published' && (
                        <Button
                          variant="secondary"
                          disabled={unpublishMutation.isPending}
                          onClick={() => unpublishMutation.mutate(entry.id)}
                        >
                          Unpublish
                        </Button>
                      )}
                      {canDelete && (
                        <Button variant="danger" onClick={() => setPendingDeleteId(entry.id)}>
                          Delete
                        </Button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <ConfirmDialog
        open={pendingDeleteId !== null}
        title="Delete entry"
        description="This permanently removes the entry."
        confirmLabel="Delete"
        loading={deleteMutation.isPending}
        onCancel={() => setPendingDeleteId(null)}
        onConfirm={() => {
          if (pendingDeleteId === null) {
            return
          }

          deleteMutation.mutate(pendingDeleteId, {
            onSuccess: () => setPendingDeleteId(null),
            onError: (error) => {
              if (error instanceof ApiError && error.status === 403) {
                setPendingDeleteId(null)
              }
            },
          })
        }}
      />
    </>
  )
}
