import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createIntegrationToken,
  createWebhook,
  deleteWebhook,
  listIntegrationScopes,
  listIntegrationTokens,
  listWebhookDeliveries,
  listWebhookEvents,
  listWebhooks,
  retryWebhookDelivery,
  revokeIntegrationToken,
  updateWebhook,
} from '../api/integrationsApi'

export function useWebhooks() {
  return useQuery({
    queryKey: ['integrations', 'webhooks'],
    queryFn: listWebhooks,
  })
}

export function useWebhookEvents() {
  return useQuery({
    queryKey: ['integrations', 'webhook-events'],
    queryFn: listWebhookEvents,
  })
}

export function useWebhookDeliveries(webhookId: number | undefined) {
  return useQuery({
    queryKey: ['integrations', 'webhooks', webhookId, 'deliveries'],
    queryFn: () => listWebhookDeliveries(webhookId!),
    enabled: webhookId !== undefined,
  })
}

export function useIntegrationTokens() {
  return useQuery({
    queryKey: ['integrations', 'tokens'],
    queryFn: listIntegrationTokens,
  })
}

export function useIntegrationScopes() {
  return useQuery({
    queryKey: ['integrations', 'token-scopes'],
    queryFn: listIntegrationScopes,
  })
}

export function useWebhookActions() {
  const queryClient = useQueryClient()

  const invalidate = () => {
    void queryClient.invalidateQueries({ queryKey: ['integrations', 'webhooks'] })
  }

  return {
    create: useMutation({
      mutationFn: createWebhook,
      onSuccess: invalidate,
    }),
    update: useMutation({
      mutationFn: ({ id, body }: { id: number; body: Parameters<typeof updateWebhook>[1] }) =>
        updateWebhook(id, body),
      onSuccess: invalidate,
    }),
    remove: useMutation({
      mutationFn: deleteWebhook,
      onSuccess: invalidate,
    }),
    retryDelivery: useMutation({
      mutationFn: ({ webhookId, deliveryId }: { webhookId: number; deliveryId: string }) =>
        retryWebhookDelivery(webhookId, deliveryId),
      onSuccess: (_data, variables) => {
        void queryClient.invalidateQueries({
          queryKey: ['integrations', 'webhooks', variables.webhookId, 'deliveries'],
        })
      },
    }),
  }
}

export function useIntegrationTokenActions() {
  const queryClient = useQueryClient()

  return {
    create: useMutation({
      mutationFn: createIntegrationToken,
      onSuccess: () => {
        void queryClient.invalidateQueries({ queryKey: ['integrations', 'tokens'] })
      },
    }),
    revoke: useMutation({
      mutationFn: revokeIntegrationToken,
      onSuccess: () => {
        void queryClient.invalidateQueries({ queryKey: ['integrations', 'tokens'] })
      },
    }),
  }
}
