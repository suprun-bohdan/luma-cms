import { useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { Checkbox } from '../../../shared/components/Checkbox'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import {
  defaultContactFields,
  formFormSchema,
  type FormFieldFormValues,
  type FormFormValues,
} from '../schemas/form'

type FormFormProps = {
  initialValues?: Partial<FormFormValues>
  submitLabel: string
  loading?: boolean
  onSubmit: (values: FormFormValues) => void
}

const defaultValues: FormFormValues = {
  name: '',
  slug: '',
  description: '',
  is_active: true,
  fields: defaultContactFields,
}

export function FormForm({
  initialValues,
  submitLabel,
  loading = false,
  onSubmit,
}: FormFormProps) {
  const [name, setName] = useState(initialValues?.name ?? defaultValues.name)
  const [slug, setSlug] = useState(initialValues?.slug ?? defaultValues.slug)
  const [description, setDescription] = useState(initialValues?.description ?? defaultValues.description)
  const [isActive, setIsActive] = useState(initialValues?.is_active ?? defaultValues.is_active)
  const [fields, setFields] = useState<FormFieldFormValues[]>(
    initialValues?.fields ?? defaultValues.fields,
  )
  const [formError, setFormError] = useState<string | null>(null)

  function updateField(index: number, patch: Partial<FormFieldFormValues>) {
    setFields((current) =>
      current.map((field, fieldIndex) => (fieldIndex === index ? { ...field, ...patch } : field)),
    )
  }

  function addField() {
    setFields((current) => [
      ...current,
      { name: 'field', label: 'Field', type: 'text', required: false },
    ])
  }

  function removeField(index: number) {
    setFields((current) => current.filter((_, fieldIndex) => fieldIndex !== index))
  }

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()
    setFormError(null)

    const parsed = formFormSchema.safeParse({
      name,
      slug,
      description,
      is_active: isActive,
      fields,
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
        label="Name"
        id="form-name"
        value={name}
        onChange={(event) => setName(event.target.value)}
        required
      />

      <Input
        label="Slug"
        id="form-slug"
        className="font-mono"
        value={slug}
        onChange={(event) => setSlug(event.target.value)}
        required
      />
      <HelpText>
        Used in the Contact form block and public submission URL. Use lowercase letters, numbers, and
        hyphens only.
      </HelpText>

      <Input
        label="Description"
        id="form-description"
        value={description}
        onChange={(event) => setDescription(event.target.value)}
      />

      <Checkbox label="Active" checked={isActive} onChange={(event) => setIsActive(event.target.checked)} />
      <HelpText>
        Inactive forms are hidden from public pages and do not accept new submissions.
      </HelpText>

      <div className="space-y-4">
        <div className="flex items-center justify-between gap-4">
          <h2 className="text-sm font-semibold text-slate-900">Fields</h2>
          <Button type="button" variant="secondary" onClick={addField}>
            Add field
          </Button>
        </div>

        {fields.map((field, index) => (
          <div key={`${field.name}-${index}`} className="rounded-lg border border-slate-200 p-4 space-y-3">
            <div className="grid gap-3 md:grid-cols-2">
              <Input
                label="Name"
                id={`field-name-${index}`}
                className="font-mono"
                value={field.name}
                onChange={(event) => updateField(index, { name: event.target.value })}
                required
              />
              <Input
                label="Label"
                id={`field-label-${index}`}
                value={field.label}
                onChange={(event) => updateField(index, { label: event.target.value })}
                required
              />
            </div>

            <div className="grid gap-3 md:grid-cols-2">
              <div>
                <label htmlFor={`field-type-${index}`} className="mb-1 block text-sm font-medium text-slate-700">
                  Type
                </label>
                <select
                  id={`field-type-${index}`}
                  className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                  value={field.type}
                  onChange={(event) =>
                    updateField(index, { type: event.target.value as FormFieldFormValues['type'] })
                  }
                >
                  <option value="text">Text</option>
                  <option value="email">Email</option>
                  <option value="textarea">Textarea</option>
                </select>
              </div>
              <div className="flex items-end">
                <Checkbox
                  label="Required"
                  checked={field.required}
                  onChange={(event) => updateField(index, { required: event.target.checked })}
                />
              </div>
            </div>

            {fields.length > 1 && (
              <Button type="button" variant="ghost" onClick={() => removeField(index)}>
                Remove field
              </Button>
            )}
          </div>
        ))}
      </div>

      <Button type="submit" disabled={loading}>
        {loading ? 'Saving…' : submitLabel}
      </Button>
    </form>
  )
}
