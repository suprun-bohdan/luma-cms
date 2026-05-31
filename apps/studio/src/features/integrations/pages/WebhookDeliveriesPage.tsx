import { Link, useParams } from 'react-router-dom'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
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
import { useWebhookActions, useWebhookDeliveries, useWebhooks } from '../hooks/useIntegrations'

function statusTone(status: string): 'success' | 'warning' | 'muted' {
  if (status === 'success') {
    return 'success'
  }

  if (status === 'failed') {
    return 'warning'
  }

  return 'muted'
}

export function WebhookDeliveriesPage() {
  const { id } = useParams()
  const webhookId = Number(id)
  const webhooksQuery = useWebhooks()
  const deliveriesQuery = useWebhookDeliveries(Number.isFinite(webhookId) ? webhookId : undefined)
  const actions = useWebhookActions()
  const webhook = webhooksQuery.data?.find((item) => item.id === webhookId)

  return (
    <>
      <div className="mb-6">
        <Breadcrumbs
          items={[
            { label: 'Integrations', to: '/integrations/webhooks' },
            { label: webhook?.name ?? `Webhook #${id}`, to: `/integrations/webhooks/${id}/edit` },
            { label: 'Deliveries' },
          ]}
        />
      </div>

      <ListPage
        title="Webhook deliveries"
        description="Delivery attempts, HTTP responses, and manual retries."
        actions={
          <Link to={`/integrations/webhooks/${id}/edit`}>
            <Button variant="secondary">Edit webhook</Button>
          </Link>
        }
        loading={deliveriesQuery.isLoading}
        loadingMessage="Loading deliveries…"
        error={
          deliveriesQuery.isError ? (
            <ErrorAlert
              message={
                deliveriesQuery.error instanceof Error
                  ? deliveriesQuery.error.message
                  : 'Failed to load deliveries'
              }
            />
          ) : undefined
        }
        empty={
          deliveriesQuery.data?.length === 0 ? (
            <p className="text-sm text-slate-600">No deliveries yet for this webhook.</p>
          ) : undefined
        }
      >
        {deliveriesQuery.data && deliveriesQuery.data.length > 0 && (
          <Table>
            <TableHead>
              <TableRow>
                <TableHeaderCell>Event</TableHeaderCell>
                <TableHeaderCell>Status</TableHeaderCell>
                <TableHeaderCell>Attempts</TableHeaderCell>
                <TableHeaderCell>HTTP</TableHeaderCell>
                <TableHeaderCell>Created</TableHeaderCell>
                <TableHeaderCell />
              </TableRow>
            </TableHead>
            <TableBody>
              {deliveriesQuery.data.map((delivery) => (
                <TableRow key={delivery.id}>
                  <TableCell className="font-mono text-xs">{delivery.event}</TableCell>
                  <TableCell>
                    <Badge tone={statusTone(delivery.status)}>{delivery.status}</Badge>
                  </TableCell>
                  <TableCell>{delivery.attempts}</TableCell>
                  <TableCell>{delivery.response_status ?? '—'}</TableCell>
                  <TableCell className="text-xs text-slate-600">
                    {delivery.created_at ?? '—'}
                  </TableCell>
                  <TableCell className="text-right">
                    {delivery.status !== 'success' && (
                      <Button
                        variant="secondary"
                        disabled={actions.retryDelivery.isPending}
                        onClick={() =>
                          actions.retryDelivery.mutate({
                            webhookId,
                            deliveryId: delivery.id,
                          })
                        }
                      >
                        Retry
                      </Button>
                    )}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        )}
      </ListPage>
    </>
  )
}
