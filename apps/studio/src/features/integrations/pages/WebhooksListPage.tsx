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
import { useWebhooks } from '../hooks/useIntegrations'

export function WebhooksListPage() {
  const webhooksQuery = useWebhooks()

  return (
    <ListPage
      title="Webhooks"
      description="Outbound HTTPS notifications when content or forms change."
      actions={
        <Link to="/integrations/webhooks/new">
          <Button>Create webhook</Button>
        </Link>
      }
      loading={webhooksQuery.isLoading}
      loadingMessage="Loading webhooks…"
      error={
        webhooksQuery.isError ? (
          <ErrorAlert
            message={
              webhooksQuery.error instanceof Error
                ? webhooksQuery.error.message
                : 'Failed to load webhooks'
            }
          />
        ) : undefined
      }
      empty={
        webhooksQuery.data?.length === 0 ? (
          <EmptyState
            title="No webhooks yet"
            description="Create a webhook to notify external systems on publish or form submissions."
            action={
              <Link to="/integrations/webhooks/new">
                <Button>Create webhook</Button>
              </Link>
            }
          />
        ) : undefined
      }
    >
      {webhooksQuery.data && webhooksQuery.data.length > 0 && (
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>Name</TableHeaderCell>
              <TableHeaderCell>URL</TableHeaderCell>
              <TableHeaderCell>Events</TableHeaderCell>
              <TableHeaderCell>Active</TableHeaderCell>
              <TableHeaderCell />
            </TableRow>
          </TableHead>
          <TableBody>
            {webhooksQuery.data.map((webhook) => (
              <TableRow key={webhook.id}>
                <TableCell className="font-medium text-slate-900">{webhook.name}</TableCell>
                <TableCell className="max-w-xs truncate font-mono text-xs text-slate-600">
                  {webhook.url}
                </TableCell>
                <TableCell className="text-xs text-slate-600">{webhook.events.join(', ')}</TableCell>
                <TableCell>{webhook.is_active ? 'Yes' : 'No'}</TableCell>
                <TableCell className="space-x-2 text-right">
                  <Link to={`/integrations/webhooks/${webhook.id}/deliveries`}>
                    <Button variant="secondary">Deliveries</Button>
                  </Link>
                  <Link to={`/integrations/webhooks/${webhook.id}/edit`}>
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
