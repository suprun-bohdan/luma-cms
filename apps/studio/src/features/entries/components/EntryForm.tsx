import { useState } from 'react'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { useFields } from '../../fields/hooks/useFields'
import { DynamicFieldInput } from './DynamicFieldInput'
import type { EntryFormValues } from '../schemas/entry'

type EntryFormProps = {
  collectionSlug: string
  initialValues?: EntryFormValues
  onSubmit: (values: EntryFormValues) => void
  submitLabel: string
  loading?: boolean
}

export function EntryForm({
  collectionSlug,
  initialValues,
  onSubmit,
  submitLabel,
  loading = false,
}: EntryFormProps) {
  const fieldsQuery = useFields(collectionSlug)
  const [data, setData] = useState<Record<string, unknown>>(initialValues?.data ?? {})

  if (fieldsQuery.isLoading) {
    return <LoadingState message="Loading field schema…" />
  }

  if (fieldsQuery.isError) {
    return <ErrorAlert message={fieldsQuery.error.message} />
  }

  const fields = fieldsQuery.data ?? []

  if (fields.length === 0) {
    return (
      <ErrorAlert message="Add fields to this collection before creating entries." />
    )
  }

  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        event.preventDefault()
        onSubmit({ data })
      }}
    >
      {fields.map((field) => (
        <DynamicFieldInput
          key={field.id}
          field={field}
          value={data[field.slug]}
          onChange={(value) =>
            setData((current) => ({
              ...current,
              [field.slug]: value,
            }))
          }
        />
      ))}
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
