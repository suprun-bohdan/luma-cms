import { z } from 'zod'

export const webhookSchema = z.object({
  id: z.number(),
  name: z.string(),
  url: z.string(),
  events: z.array(z.string()),
  is_active: z.boolean(),
  created_by: z.number().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
})

export type Webhook = z.infer<typeof webhookSchema>

export const webhookDeliverySchema = z.object({
  id: z.string(),
  webhook_id: z.number(),
  event: z.string(),
  payload: z.record(z.string(), z.unknown()),
  status: z.enum(['pending', 'success', 'failed']),
  attempts: z.number(),
  response_status: z.number().nullable(),
  response_body: z.string().nullable(),
  error_message: z.string().nullable(),
  next_retry_at: z.string().nullable(),
  delivered_at: z.string().nullable(),
  created_at: z.string().nullable().optional(),
})

export type WebhookDelivery = z.infer<typeof webhookDeliverySchema>

export const integrationTokenSchema = z.object({
  id: z.number(),
  name: z.string(),
  token_prefix: z.string(),
  abilities: z.array(z.string()),
  last_used_at: z.string().nullable(),
  expires_at: z.string().nullable(),
  created_at: z.string().nullable().optional(),
})

export type IntegrationToken = z.infer<typeof integrationTokenSchema>

export const createdIntegrationTokenSchema = integrationTokenSchema.extend({
  plain_text_token: z.string(),
})

export type CreatedIntegrationToken = z.infer<typeof createdIntegrationTokenSchema>
