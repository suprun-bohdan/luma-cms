import { useState } from 'react'
import { Input } from '../../../shared/components/Input'
import { slugify } from '../../../shared/utils/format'
import { FieldTypeSelect } from './FieldTypeSelect'
import type { FieldFormValues, FieldType } from '../schemas/field'

type FieldFormProps = {
  initialValues?: FieldFormValues
  onSubmit: (values: FieldFormValues) => void
  submitLabel: string
  loading?: boolean
}

const defaultValues: FieldFormValues = {
  name: '',
  slug: '',
  type: 'text',
  required: false,
  sort_order: 0,
}

export function FieldForm({
  initialValues,
  onSubmit,
  submitLabel,
  loading = false,
}: FieldFormProps) {
  const [values, setValues] = useState<FieldFormValues>(initialValues ?? defaultValues)
  const [slugTouched, setSlugTouched] = useState(Boolean(initialValues?.slug))
  const [configText, setConfigText] = useState(
    initialValues?.config ? JSON.stringify(initialValues.config, null, 2) : '',
  )

  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        event.preventDefault()

        let config: Record<string, unknown> | null = null
        if (configText.trim()) {
          config = JSON.parse(configText) as Record<string, unknown>
        }

        onSubmit({
          ...values,
          config,
        })
      }}
    >
      <Input
        label="Name"
        value={values.name}
        onChange={(event) => {
          const name = event.target.value
          setValues((current) => ({
            ...current,
            name,
            slug: slugTouched ? current.slug : slugify(name),
          }))
        }}
        required
      />
      <Input
        label="Slug"
        value={values.slug}
        onChange={(event) => {
          setSlugTouched(true)
          setValues((current) => ({ ...current, slug: event.target.value }))
        }}
        required
      />
      <FieldTypeSelect
        value={values.type}
        onChange={(type: FieldType) => setValues((current) => ({ ...current, type }))}
      />
      <Input
        label="Sort order"
        type="number"
        min={0}
        value={values.sort_order ?? 0}
        onChange={(event) =>
          setValues((current) => ({ ...current, sort_order: Number(event.target.value) }))
        }
      />
      <label className="flex items-center gap-2 text-sm text-slate-700">
        <input
          type="checkbox"
          checked={values.required ?? false}
          onChange={(event) =>
            setValues((current) => ({ ...current, required: event.target.checked }))
          }
        />
        Required field
      </label>
      <label className="block space-y-1.5">
        <span className="text-sm font-medium text-slate-700">Config (JSON, optional)</span>
        <textarea
          className="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"
          rows={4}
          value={configText}
          onChange={(event) => setConfigText(event.target.value)}
        />
      </label>
      <button
        type="submit"
        disabled={loading}
        className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
      >
        {loading ? 'Saving…' : submitLabel}
      </button>
    </form>
  )
}
