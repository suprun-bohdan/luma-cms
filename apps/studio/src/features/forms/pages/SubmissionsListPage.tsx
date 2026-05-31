import { Link, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
import { useFormSubmissions } from '../hooks/useForms'

function formatSubmissionPreview(data: Record<string, unknown>): string {
  const parts = Object.entries(data).slice(0, 3).map(([key, value]) => {
    const text = typeof value === 'string' ? value : JSON.stringify(value)
    const trimmed = text.length > 40 ? `${text.slice(0, 40)}…` : text
    return `${key}: ${trimmed}`
  })

  return parts.join(' · ')
}

export function SubmissionsListPage() {
  const { slug } = useParams()
  const submissionsQuery = useFormSubmissions(slug)

  if (!slug) {
    return <ErrorAlert message="Form slug is required." />
  }

  return (
    <>
      <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
          <Breadcrumbs
            items={[
              { label: 'Forms', to: '/forms' },
              { label: slug, to: `/forms/${slug}/edit` },
              { label: 'Submissions' },
            ]}
          />
          <h1 className="mt-2 text-2xl font-semibold text-slate-900">Submissions</h1>
          <p className="mt-1 text-sm text-slate-600">Read-only inbox for form `{slug}`.</p>
        </div>

        <Link to={`/forms/${slug}/edit`}>
          <Button variant="secondary">Edit form</Button>
        </Link>
      </div>

      {submissionsQuery.isLoading && <LoadingState message="Loading submissions…" />}
      {submissionsQuery.isError && <ErrorAlert message={submissionsQuery.error.message} />}

      {submissionsQuery.data?.length === 0 && (
        <EmptyState
          title="No submissions yet"
          description="Publish a page with a contact_form block pointing at this form slug."
        />
      )}

      {submissionsQuery.data && submissionsQuery.data.length > 0 && (
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>Received</TableHeaderCell>
              <TableHeaderCell>Preview</TableHeaderCell>
              <TableHeaderCell>IP</TableHeaderCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {submissionsQuery.data.map((submission) => (
              <TableRow key={submission.id}>
                <TableCell className="whitespace-nowrap text-slate-600">
                  {submission.created_at
                    ? new Date(submission.created_at).toLocaleString()
                    : '—'}
                </TableCell>
                <TableCell className="text-slate-900">
                  {formatSubmissionPreview(submission.data)}
                </TableCell>
                <TableCell className="font-mono text-slate-500">{submission.ip ?? '—'}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}
    </>
  )
}
