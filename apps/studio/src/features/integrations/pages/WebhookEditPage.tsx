import { useMemo } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { ApiError } from '../../../shared/api/client'
import { Button } from '../../../shared/components/Button'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { formatFieldErrors } from '../../../shared/utils/format'
import { WebhookForm } from '../components/WebhookForm'
import { webhookToFormValues } from '../schemas/webhookForm'
import { useWebhookActions, useWebhookEvents, useWebhooks } from '../hooks/useIntegrations'

export function WebhookEditPage() {
  const navigate = useNavigate()
  const { id } = useParams()
  const isNew = !id || id === 'new'
  const webhookId = isNew ? undefined : Number(id)
  const webhooksQuery = useWebhooks()
  const eventsQuery = useWebhookEvents()
  const actions = useWebhookActions()

  const webhook = useMemo(
    () => webhooksQuery.data?.find((item) => item.id === webhookId),
    [webhooksQuery.data, webhookId],
  )

  const mutation = isNew ? actions.create : actions.update
  const errorMessage =
    mutation.error instanceof ApiError
      ? formatFieldErrors(mutation.error.errors) || mutation.error.message
      : mutation.error instanceof Error
        ? mutation.error.message
        : null

  return (
    <>
      <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
          <Breadcrumbs
            items={[
              { label: 'Integrations', to: '/integrations/webhooks' },
              { label: isNew ? 'New webhook' : webhook?.name ?? `Webhook #${id}` },
            ]}
          />
          <h1 className="mt-2 text-2xl font-semibold text-slate-900">
            {isNew ? 'Create webhook' : 'Edit webhook'}
          </h1>
        </div>

        {!isNew && webhookId !== undefined && (
          <div className="flex gap-2">
            <Link to={`/integrations/webhooks/${webhookId}/deliveries`}>
              <Button variant="secondary">Deliveries</Button>
            </Link>
            <Button
              variant="danger"
              disabled={actions.remove.isPending}
              onClick={() =>
                actions.remove.mutate(webhookId, {
                  onSuccess: () => navigate('/integrations/webhooks'),
                })
              }
            >
              Delete
            </Button>
          </div>
        )}
      </div>

      {!isNew && webhooksQuery.isLoading && <LoadingState message="Loading webhook…" />}
      {!isNew && webhooksQuery.isSuccess && !webhook && (
        <ErrorAlert message="Webhook not found." />
      )}
      {eventsQuery.isError && <ErrorAlert message={eventsQuery.error.message} />}

      {(isNew || webhook) && eventsQuery.data && (
        <Card>
          {errorMessage && (
            <div className="mb-4">
              <ErrorAlert message={errorMessage} />
            </div>
          )}

          <WebhookForm
            key={isNew ? 'new' : webhook!.id}
            availableEvents={eventsQuery.data}
            initialValues={isNew ? undefined : webhookToFormValues(webhook!)}
            submitLabel={isNew ? 'Create webhook' : 'Save changes'}
            loading={mutation.isPending}
            onSubmit={(values) => {
              if (isNew) {
                actions.create.mutate(values, {
                  onSuccess: (created) => navigate(`/integrations/webhooks/${created.id}/edit`),
                })
                return
              }

              actions.update.mutate(
                { id: webhookId!, body: values },
                { onSuccess: () => webhooksQuery.refetch() },
              )
            }}
          />
        </Card>
      )}
    </>
  )
}
