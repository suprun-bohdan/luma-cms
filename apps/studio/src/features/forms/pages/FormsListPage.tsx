import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { HelpText } from '../../../shared/components/HelpText'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
import { ListPage } from '../../../shared/layout'
import { useForms } from '../hooks/useForms'

export function FormsListPage() {
  const formsQuery = useForms()

  return (
    <ListPage
      title="Forms"
      description="Manage contact forms and view submissions from public pages."
      actions={
        <Link to="/forms/new">
          <Button>Create form</Button>
        </Link>
      }
      loading={formsQuery.isLoading}
      loadingMessage="Loading forms…"
      error={
        formsQuery.isError ? (
          <ErrorAlert
            message={
              formsQuery.error instanceof Error ? formsQuery.error.message : 'Failed to load forms'
            }
          />
        ) : undefined
      }
      empty={
        formsQuery.data?.length === 0 ? (
          <EmptyState
            title="No forms yet"
            description="Create a form, then add it to a page using the Contact form block."
            action={
              <Link to="/forms/new">
                <Button>Create form</Button>
              </Link>
            }
          />
        ) : undefined
      }
    >
      <HelpText className="mb-4">
        Active forms accept public submissions on pages where you embed them. Inactive forms stay
        hidden from visitors.
      </HelpText>
      {formsQuery.data && formsQuery.data.length > 0 && (
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>Name</TableHeaderCell>
              <TableHeaderCell>Public URL slug</TableHeaderCell>
              <TableHeaderCell>Fields</TableHeaderCell>
              <TableHeaderCell>Accepting submissions</TableHeaderCell>
              <TableHeaderCell />
            </TableRow>
          </TableHead>
          <TableBody>
            {formsQuery.data.map((form) => (
              <TableRow key={form.id}>
                <TableCell className="font-medium text-slate-900">{form.name}</TableCell>
                <TableCell className="font-mono text-slate-600">{form.slug}</TableCell>
                <TableCell>{form.fields.length}</TableCell>
                <TableCell>{form.is_active ? 'Yes' : 'No (hidden)'}</TableCell>
                <TableCell className="text-right space-x-2">
                  <Link to={`/forms/${form.slug}/submissions`}>
                    <Button variant="secondary">Submissions</Button>
                  </Link>
                  <Link to={`/forms/${form.slug}/edit`}>
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
