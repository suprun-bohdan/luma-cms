import { Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { Button } from '../../../shared/components/Button'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
import { ListPage } from '../../../shared/layout'
import { listAuditLogs } from '../api/pluginsApi'

function formatMetadata(metadata: Record<string, unknown> | null): string {
  if (!metadata || Object.keys(metadata).length === 0) {
    return '—'
  }

  const text = JSON.stringify(metadata)
  return text.length > 80 ? `${text.slice(0, 80)}…` : text
}

export function AuditLogsListPage() {
  const auditLogsQuery = useQuery({
    queryKey: ['audit-logs'],
    queryFn: listAuditLogs,
    retry: false,
  })

  return (
    <ListPage
      title="Plugin audit log"
      description="Lifecycle and capability events recorded by the plugin system."
      breadcrumbs={
        <Breadcrumbs
          items={[
            { label: 'Plugins', to: '/plugins' },
            { label: 'Audit log' },
          ]}
        />
      }
      actions={
        <Link to="/plugins">
          <Button variant="secondary">Back to plugins</Button>
        </Link>
      }
      loading={auditLogsQuery.isLoading}
      loadingMessage="Loading audit log…"
      error={
        auditLogsQuery.isError ? (
          <ErrorAlert
            message={
              auditLogsQuery.error instanceof Error
                ? auditLogsQuery.error.message
                : 'Failed to load audit log'
            }
          />
        ) : undefined
      }
    >
      {auditLogsQuery.data && auditLogsQuery.data.length > 0 && (
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>When</TableHeaderCell>
              <TableHeaderCell>Action</TableHeaderCell>
              <TableHeaderCell>Subject</TableHeaderCell>
              <TableHeaderCell>Actor</TableHeaderCell>
              <TableHeaderCell>Metadata</TableHeaderCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {auditLogsQuery.data.map((log) => (
              <TableRow key={log.id}>
                <TableCell className="whitespace-nowrap text-slate-600">
                  {log.created_at ? new Date(log.created_at).toLocaleString() : '—'}
                </TableCell>
                <TableCell className="font-mono text-xs">{log.action}</TableCell>
                <TableCell className="font-mono text-xs text-slate-600">
                  {log.subject_type ?? '—'}
                  {log.subject_id ? ` · ${log.subject_id}` : ''}
                </TableCell>
                <TableCell>{log.actor?.name ?? '—'}</TableCell>
                <TableCell className="max-w-xs truncate font-mono text-xs text-slate-500">
                  {formatMetadata(log.metadata)}
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}

      {auditLogsQuery.data?.length === 0 && (
        <p className="text-sm text-slate-500">No audit events recorded yet.</p>
      )}
    </ListPage>
  )
}
