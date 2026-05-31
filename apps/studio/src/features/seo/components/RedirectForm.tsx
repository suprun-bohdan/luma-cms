import { useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { Checkbox } from '../../../shared/components/Checkbox'
import { Input } from '../../../shared/components/Input'
import { redirectFormSchema, type RedirectFormValues } from '../schemas/redirect'

type RedirectFormProps = {
  initialValues?: Partial<RedirectFormValues>
  submitLabel: string
  loading?: boolean
  onSubmit: (values: RedirectFormValues) => void
}

const defaultValues: RedirectFormValues = {
  from_path: '',
  to_path: '',
  to_url: '',
  status_code: 301,
  is_active: true,
}

export function RedirectForm({
  initialValues,
  submitLabel,
  loading = false,
  onSubmit,
}: RedirectFormProps) {
  const [fromPath, setFromPath] = useState(initialValues?.from_path ?? defaultValues.from_path)
  const [toPath, setToPath] = useState(initialValues?.to_path ?? defaultValues.to_path)
  const [toUrl, setToUrl] = useState(initialValues?.to_url ?? defaultValues.to_url)
  const [statusCode, setStatusCode] = useState(initialValues?.status_code ?? defaultValues.status_code)
  const [isActive, setIsActive] = useState(initialValues?.is_active ?? defaultValues.is_active)
  const [formError, setFormError] = useState<string | null>(null)

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()
    setFormError(null)

    const parsed = redirectFormSchema.safeParse({
      from_path: fromPath,
      to_path: toPath,
      to_url: toUrl,
      status_code: statusCode,
      is_active: isActive,
    })

    if (!parsed.success) {
      setFormError(parsed.error.issues.map((issue) => issue.message).join(' '))
      return
    }

    onSubmit(parsed.data)
  }

  return (
    <form className="space-y-6" onSubmit={handleSubmit}>
      {formError && <p className="text-sm text-red-600">{formError}</p>}

      <Input
        label="From path"
        id="redirect-from-path"
        placeholder="/old-page"
        className="font-mono"
        value={fromPath}
        onChange={(event) => setFromPath(event.target.value)}
        required
      />

      <Input
        label="To path (internal)"
        id="redirect-to-path"
        placeholder="/p/home"
        className="font-mono"
        value={toPath}
        onChange={(event) => setToPath(event.target.value)}
      />

      <Input
        label="To URL (external)"
        id="redirect-to-url"
        placeholder="https://example.com"
        value={toUrl}
        onChange={(event) => setToUrl(event.target.value)}
      />

      <div>
        <label htmlFor="redirect-status-code" className="mb-1 block text-sm font-medium text-slate-700">
          Status code
        </label>
        <select
          id="redirect-status-code"
          className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
          value={statusCode}
          onChange={(event) => setStatusCode(Number(event.target.value) as 301 | 302)}
        >
          <option value={301}>301 Permanent</option>
          <option value={302}>302 Temporary</option>
        </select>
      </div>

      <Checkbox
        label="Active"
        checked={isActive}
        onChange={(event) => setIsActive(event.target.checked)}
      />

      <Button type="submit" disabled={loading}>
        {loading ? 'Saving…' : submitLabel}
      </Button>
    </form>
  )
}
