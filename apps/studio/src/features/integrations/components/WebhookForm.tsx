import { useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { Checkbox } from '../../../shared/components/Checkbox'
import { Input } from '../../../shared/components/Input'
import type { WebhookFormValues } from '../schemas/webhookForm'

type WebhookFormProps = {
  initialValues?: Partial<WebhookFormValues>
  availableEvents: string[]
  submitLabel: string
  loading?: boolean
  onSubmit: (values: WebhookFormValues) => void
}

export function WebhookForm({
  initialValues,
  availableEvents,
  submitLabel,
  loading,
  onSubmit,
}: WebhookFormProps) {
  const [name, setName] = useState(initialValues?.name ?? '')
  const [url, setUrl] = useState(initialValues?.url ?? '')
  const [events, setEvents] = useState<string[]>(initialValues?.events ?? [])
  const [isActive, setIsActive] = useState(initialValues?.is_active ?? true)

  function toggleEvent(event: string) {
    setEvents((current) =>
      current.includes(event) ? current.filter((item) => item !== event) : [...current, event],
    )
  }

  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        event.preventDefault()
        onSubmit({ name, url, events, is_active: isActive })
      }}
    >
      <Input label="Name" value={name} onChange={(event) => setName(event.target.value)} required />
      <Input
        label="HTTPS URL"
        type="url"
        value={url}
        onChange={(event) => setUrl(event.target.value)}
        placeholder="https://example.com/webhooks/luma"
        required
      />

      <fieldset className="space-y-2">
        <legend className="text-sm font-medium text-slate-900">Events</legend>
        {availableEvents.map((event) => (
          <Checkbox
            key={event}
            label={event}
            checked={events.includes(event)}
            onChange={() => toggleEvent(event)}
          />
        ))}
      </fieldset>

      <Checkbox
        label="Active"
        checked={isActive}
        onChange={(event) => setIsActive(event.target.checked)}
      />

      <Button type="submit" disabled={loading || events.length === 0}>
        {submitLabel}
      </Button>
    </form>
  )
}
