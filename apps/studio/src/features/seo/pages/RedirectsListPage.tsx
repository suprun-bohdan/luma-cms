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
import { useRedirects } from '../hooks/useRedirects'

export function RedirectsListPage() {
  const redirectsQuery = useRedirects()

  return (
    <ListPage
      title="URL redirects"
      description="Manage HTTP redirects for legacy paths and external links."
      actions={
        <Link to="/seo/redirects/new">
          <Button>Create redirect</Button>
        </Link>
      }
      loading={redirectsQuery.isLoading}
      loadingMessage="Loading redirects…"
      error={
        redirectsQuery.isError ? (
          <ErrorAlert
            message={
              redirectsQuery.error instanceof Error
                ? redirectsQuery.error.message
                : 'Failed to load redirects'
            }
          />
        ) : undefined
      }
      empty={
        redirectsQuery.data?.length === 0 ? (
          <EmptyState
            title="No redirects yet"
            description="Create a redirect when you rename a page or move content."
            action={
              <Link to="/seo/redirects/new">
                <Button>Create redirect</Button>
              </Link>
            }
          />
        ) : undefined
      }
    >
      {redirectsQuery.data && redirectsQuery.data.length > 0 && (
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>From</TableHeaderCell>
              <TableHeaderCell>Target</TableHeaderCell>
              <TableHeaderCell>Code</TableHeaderCell>
              <TableHeaderCell>Active</TableHeaderCell>
              <TableHeaderCell />
            </TableRow>
          </TableHead>
          <TableBody>
            {redirectsQuery.data.map((redirect) => (
              <TableRow key={redirect.id}>
                <TableCell className="font-mono text-slate-900">{redirect.from_path}</TableCell>
                <TableCell className="font-mono text-slate-600">
                  {redirect.to_path ?? redirect.to_url}
                </TableCell>
                <TableCell>{redirect.status_code}</TableCell>
                <TableCell>{redirect.is_active ? 'Yes' : 'No'}</TableCell>
                <TableCell className="text-right">
                  <Link to={`/seo/redirects/${redirect.id}/edit`}>
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
