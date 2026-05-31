import { useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
import { PreviewPane, SettingsPanel, SplitPane } from '../../../shared/layout'
import { formatDate } from '../../../shared/utils/format'
import { useCollection, useCollections } from '../../collections/hooks/useCollections'
import { useFields } from '../../fields/hooks/useFields'
import { getPublicEntry } from '../../entries/api/entriesApi'
import { EntryStatusBadge } from '../../entries/components/EntryStatusBadge'
import { useEntry } from '../../entries/hooks/useEntries'
import { useQuery } from '@tanstack/react-query'

type PreviewMode = 'admin' | 'public'

function renderValue(value: unknown): string {
  if (value === null || value === undefined) {
    return '—'
  }

  if (typeof value === 'object') {
    return JSON.stringify(value, null, 2)
  }

  return String(value)
}

export function EntryPreviewPage() {
  const { id } = useParams()
  const entryId = Number(id)
  const [mode, setMode] = useState<PreviewMode>('admin')
  const entryQuery = useEntry(entryId)
  const collectionsQuery = useCollections()
  const collectionSlug = useMemo(() => {
    if (!entryQuery.data || !collectionsQuery.data) {
      return undefined
    }

    return collectionsQuery.data.find(
      (collection) => collection.id === entryQuery.data!.collection_id,
    )?.slug
  }, [collectionsQuery.data, entryQuery.data])
  const collectionQuery = useCollection(collectionSlug)
  const fieldsQuery = useFields(collectionSlug)

  const publicQuery = useQuery({
    queryKey: ['entries', 'public', entryId],
    queryFn: () => getPublicEntry(entryId),
    enabled: mode === 'public' && entryQuery.data?.status === 'published',
    retry: false,
  })

  const previewEntry =
    mode === 'public' && entryQuery.data?.status === 'published'
      ? publicQuery.data ?? entryQuery.data
      : entryQuery.data

  const fieldLabels = useMemo(() => {
    const labels = new Map<string, string>()
    fieldsQuery.data?.forEach((field) => labels.set(field.slug, field.name))
    return labels
  }, [fieldsQuery.data])

  return (
    <>
      <PageHeader
        title={`Preview entry #${entryId}`}
        breadcrumbs={
          <Breadcrumbs
            items={[
              { label: 'Collections', to: '/collections' },
              ...(collectionSlug
                ? [
                    { label: collectionSlug, to: `/collections/${collectionSlug}/edit` },
                    { label: 'Entries', to: `/collections/${collectionSlug}/entries` },
                  ]
                : []),
              { label: `#${entryId}`, to: `/entries/${entryId}/edit` },
              { label: 'Preview' },
            ]}
          />
        }
        actions={
          <div className="flex flex-wrap gap-2">
            <Button
              variant={mode === 'admin' ? 'primary' : 'secondary'}
              onClick={() => setMode('admin')}
            >
              Admin view
            </Button>
            <Button
              variant={mode === 'public' ? 'primary' : 'secondary'}
              disabled={entryQuery.data?.status !== 'published'}
              onClick={() => setMode('public')}
            >
              Public view
            </Button>
            <Link to={`/entries/${entryId}/edit`}>
              <Button variant="secondary">Edit</Button>
            </Link>
          </div>
        }
      />

      {entryQuery.isLoading && <LoadingState message="Loading entry…" />}
      {entryQuery.isError && <ErrorAlert message={entryQuery.error.message} />}

      {mode === 'public' && entryQuery.data?.status !== 'published' && (
        <ErrorAlert message="Public preview is only available for published entries." />
      )}

      {mode === 'public' && publicQuery.isError && entryQuery.data?.status === 'published' && (
        <ErrorAlert message={publicQuery.error.message} />
      )}

      {previewEntry && (
        <SplitPane
          left={
            <SettingsPanel title="Entry metadata">
              <div className="grid gap-4">
                <div>
                  <p className="text-xs uppercase text-slate-500">Status</p>
                  <EntryStatusBadge status={previewEntry.status} />
                </div>
                <div>
                  <p className="text-xs uppercase text-slate-500">Collection</p>
                  <p className="text-sm font-medium">
                    {collectionQuery.data?.name ?? collectionSlug}
                  </p>
                </div>
                <div>
                  <p className="text-xs uppercase text-slate-500">Published at</p>
                  <p className="text-sm">{formatDate(previewEntry.published_at)}</p>
                </div>
                <div>
                  <p className="text-xs uppercase text-slate-500">Updated</p>
                  <p className="text-sm">{formatDate(previewEntry.updated_at)}</p>
                </div>
                {collectionQuery.data && (
                  <div>
                    <p className="text-xs uppercase text-slate-500">Schema version</p>
                    <p className="text-sm">v{collectionQuery.data.schema_version}</p>
                  </div>
                )}
              </div>
            </SettingsPanel>
          }
          right={
            <PreviewPane title="Content">
              <Table>
                <TableHead>
                  <TableRow>
                    <TableHeaderCell>Field</TableHeaderCell>
                    <TableHeaderCell>Value</TableHeaderCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {Object.entries(previewEntry.data).map(([key, value]) => (
                    <TableRow key={key}>
                      <TableCell className="font-medium">
                        {fieldLabels.get(key) ?? key}
                      </TableCell>
                      <TableCell className="whitespace-pre-wrap font-mono text-xs">
                        {renderValue(value)}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </PreviewPane>
          }
        />
      )}
    </>
  )
}
