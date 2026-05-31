import type { Webhook } from '../schemas/integration'

export type WebhookFormValues = {
  name: string
  url: string
  events: string[]
  is_active: boolean
}

export function webhookToFormValues(webhook: Webhook): WebhookFormValues {
  return {
    name: webhook.name,
    url: webhook.url,
    events: webhook.events,
    is_active: webhook.is_active,
  }
}
