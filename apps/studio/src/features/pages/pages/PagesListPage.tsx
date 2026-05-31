import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
import { ListPage } from '../../../shared/layout'
import { formatDate } from '../../../shared/utils/format'
import { PageStatusBadge } from '../components/PageStatusBadge'
import { usePages } from '../hooks/usePages'

export function PagesListPage() {
  const pagesQuery = usePages()

  return (
    <ListPage
      title="Pages"
      description="Static site pages with block-based content."
      actions={
        <Link to="/pages/new">
          <Button>Create page</Button>
        </Link>
      }
      loading={pagesQuery.isLoading}
      loadingMessage="Loading pages…"
      error={
        pagesQuery.isError ? (
          <ErrorAlert
            message={
              pagesQuery.error instanceof Error
                ? pagesQuery.error.message
                : 'Failed to load pages'
            }
          />
        ) : undefined
      }
      empty={
        pagesQuery.data?.length === 0 ? (
          <EmptyState
            title="No pages yet"
            description="Create your first page to build the public site."
            action={
              <Link to="/pages/new">
                <Button>Create page</Button>
              </Link>
            }
          />
        ) : undefined
      }
    >
      {pagesQuery.data && pagesQuery.data.length > 0 && (
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>Title</TableHeaderCell>
              <TableHeaderCell>Slug</TableHeaderCell>
              <TableHeaderCell>Status</TableHeaderCell>
              <TableHeaderCell>Updated</TableHeaderCell>
              <TableHeaderCell />
            </TableRow>
          </TableHead>
          <TableBody>
            {pagesQuery.data.map((page) => (
              <TableRow key={page.id}>
                <TableCell className="font-medium text-slate-900">{page.title}</TableCell>
                <TableCell className="font-mono text-slate-600">{page.slug}</TableCell>
                <TableCell>
                  <PageStatusBadge status={page.status} />
                </TableCell>
                <TableCell className="text-slate-500">{formatDate(page.updated_at)}</TableCell>
                <TableCell className="text-right">
                  <Link to={`/pages/${page.slug}/edit`}>
                    <Button variant="secondary">Edit</Button>
                  </Link>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}
    </ListPage>
  )
}
