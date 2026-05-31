import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { formatDate } from '../../../shared/utils/format'
import { PageStatusBadge } from '../components/PageStatusBadge'
import { usePages } from '../hooks/usePages'

export function PagesListPage() {
  const pagesQuery = usePages()

  return (
    <>
      <PageHeader
        title="Pages"
        description="Static site pages with block-based content."
        actions={
          <Link to="/pages/new">
            <Button>Create page</Button>
          </Link>
        }
      />

      {pagesQuery.isLoading && <LoadingState message="Loading pages…" />}
      {pagesQuery.isError && (
        <ErrorAlert
          message={
            pagesQuery.error instanceof Error
              ? pagesQuery.error.message
              : 'Failed to load pages'
          }
        />
      )}

      {pagesQuery.data?.length === 0 && (
        <EmptyState
          title="No pages yet"
          description="Create your first page to build the public site."
          action={
            <Link to="/pages/new">
              <Button>Create page</Button>
            </Link>
          }
        />
      )}

      {pagesQuery.data && pagesQuery.data.length > 0 && (
        <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
          <table className="min-w-full divide-y divide-slate-200 text-sm">
            <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
              <tr>
                <th className="px-4 py-3">Title</th>
                <th className="px-4 py-3">Slug</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Updated</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {pagesQuery.data.map((page) => (
                <tr key={page.id}>
                  <td className="px-4 py-3 font-medium text-slate-900">{page.title}</td>
                  <td className="px-4 py-3 font-mono text-slate-600">{page.slug}</td>
                  <td className="px-4 py-3">
                    <PageStatusBadge status={page.status} />
                  </td>
                  <td className="px-4 py-3 text-slate-500">{formatDate(page.updated_at)}</td>
                  <td className="px-4 py-3 text-right">
                    <Link to={`/pages/${page.slug}/edit`}>
                      <Button variant="secondary">Edit</Button>
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </>
  )
}
