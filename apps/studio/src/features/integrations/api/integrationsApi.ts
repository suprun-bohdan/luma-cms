import { z } from 'zod'
import { apiGetList, apiRequest, wrappedListSchema } from '../../../shared/api/client'
import {
  createdIntegrationTokenSchema,
  integrationTokenSchema,
  webhookDeliverySchema,
  webhookSchema,
  type CreatedIntegrationToken,
  type IntegrationToken,
  type Webhook,
  type WebhookDelivery,
} from '../schemas/integration'

export async function listWebhooks(): Promise<Webhook[]> {
  return apiGetList('/api/v1/integrations/webhooks', webhookSchema, { auth: true })
}

export async function listWebhookEvents(): Promise<string[]> {
  const response = await apiRequest({
    method: 'GET',
    path: '/api/v1/integrations/webhooks/events',
    schema: z.object({ data: z.array(z.string()) }),
    auth: true,
  })

  return response.data
}

export async function createWebhook(body: {
  name: string
  url: string
  events: string[]
  is_active?: boolean
}): Promise<Webhook> {
  return apiRequest({
    method: 'POST',
    path: '/api/v1/integrations/webhooks',
    body,
    schema: webhookSchema,
    auth: true,
  })
}

export async function updateWebhook(
  id: number,
  body: Partial<{ name: string; url: string; events: string[]; is_active: boolean }>,
): Promise<Webhook> {
  return apiRequest({
    method: 'PUT',
    path: `/api/v1/integrations/webhooks/${id}`,
    body,
    schema: webhookSchema,
    auth: true,
  })
}

export async function deleteWebhook(id: number): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/integrations/webhooks/${id}`,
    schema: z.null(),
    auth: true,
  })
}

export async function listWebhookDeliveries(webhookId: number): Promise<WebhookDelivery[]> {
  const response = await apiRequest({
    method: 'GET',
    path: `/api/v1/integrations/webhooks/${webhookId}/deliveries`,
    schema: wrappedListSchema(webhookDeliverySchema),
    auth: true,
  })

  return response.data
}

export async function retryWebhookDelivery(
  webhookId: number,
  deliveryId: string,
): Promise<WebhookDelivery> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/integrations/webhooks/${webhookId}/deliveries/${deliveryId}/retry`,
    schema: webhookDeliverySchema,
    auth: true,
  })
}

export async function listIntegrationTokens(): Promise<IntegrationToken[]> {
  return apiGetList('/api/v1/integrations/tokens', integrationTokenSchema, { auth: true })
}

export async function listIntegrationScopes(): Promise<string[]> {
  const response = await apiRequest({
    method: 'GET',
    path: '/api/v1/integrations/tokens/scopes',
    schema: z.object({ data: z.array(z.string()) }),
    auth: true,
  })

  return response.data
}

export async function createIntegrationToken(body: {
  name: string
  abilities: string[]
  expires_at?: string | null
}): Promise<CreatedIntegrationToken> {
  const response = await apiRequest({
    method: 'POST',
    path: '/api/v1/integrations/tokens',
    body,
    schema: z.object({ data: createdIntegrationTokenSchema }),
    auth: true,
  })

  return response.data
}

export async function revokeIntegrationToken(id: number): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/integrations/tokens/${id}`,
    schema: z.null(),
    auth: true,
  })
}
