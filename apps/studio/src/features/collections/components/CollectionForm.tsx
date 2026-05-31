import { useState } from 'react'
import { Input } from '../../../shared/components/Input'
import { Textarea } from '../../../shared/components/Textarea'
import { slugify } from '../../../shared/utils/format'
import type { CollectionFormValues } from '../schemas/collection'

type CollectionFormProps = {
  initialValues?: CollectionFormValues
  onSubmit: (values: CollectionFormValues) => void
  submitLabel: string
  loading?: boolean
}

const defaultValues: CollectionFormValues = {
  name: '',
  slug: '',
  description: '',
}

export function CollectionForm({
  initialValues,
  onSubmit,
  submitLabel,
  loading = false,
}: CollectionFormProps) {
  const [values, setValues] = useState<CollectionFormValues>(initialValues ?? defaultValues)
  const [slugTouched, setSlugTouched] = useState(Boolean(initialValues?.slug))

  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        event.preventDefault()
        onSubmit({
          ...values,
          description: values.description?.trim() ? values.description : undefined,
        })
      }}
    >
      <Input
        label="Name"
        name="name"
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
        name="slug"
        value={values.slug}
        onChange={(event) => {
          setSlugTouched(true)
          setValues((current) => ({ ...current, slug: event.target.value }))
        }}
        required
      />
      <Textarea
        label="Description"
        name="description"
        value={values.description ?? ''}
        onChange={(event) =>
          setValues((current) => ({ ...current, description: event.target.value }))
        }
      />
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
