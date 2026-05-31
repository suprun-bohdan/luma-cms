import { z } from 'zod'

export const formFieldTypeSchema = z.enum(['text', 'email', 'textarea'])

export const formFieldSchema = z.object({
  id: z.number().optional(),
  name: z.string(),
  label: z.string(),
  type: formFieldTypeSchema,
  required: z.boolean(),
  sort_order: z.number(),
})

export const formSchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string(),
  description: z.string().nullable(),
  is_active: z.boolean(),
  fields: z.array(formFieldSchema),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Form = z.infer<typeof formSchema>
export type FormField = z.infer<typeof formFieldSchema>

export const formFieldFormSchema = z.object({
  name: z.string().min(1, 'Field name is required').regex(/^[a-z0-9_]+$/, 'Use lowercase letters, numbers, and underscores'),
  label: z.string().min(1, 'Label is required'),
  type: formFieldTypeSchema,
  required: z.boolean(),
})

export const formFormSchema = z.object({
  name: z.string().min(1, 'Name is required'),
  slug: z.string().min(1, 'Slug is required').regex(/^[a-z0-9-]+$/, 'Use lowercase letters, numbers, and hyphens'),
  description: z.string().optional(),
  is_active: z.boolean(),
  fields: z.array(formFieldFormSchema).min(1, 'Add at least one field'),
})

export type FormFormValues = z.infer<typeof formFormSchema>
export type FormFieldFormValues = z.infer<typeof formFieldFormSchema>

export const formSubmissionSchema = z.object({
  id: z.number(),
  form_id: z.number(),
  data: z.record(z.string(), z.unknown()),
  ip: z.string().nullable(),
  user_agent: z.string().nullable(),
  created_at: z.string().nullable(),
})

export type FormSubmission = z.infer<typeof formSubmissionSchema>

export function formToFormValues(form: Form): FormFormValues {
  return {
    name: form.name,
    slug: form.slug,
    description: form.description ?? '',
    is_active: form.is_active,
    fields: form.fields.map((field) => ({
      name: field.name,
      label: field.label,
      type: field.type,
      required: field.required,
    })),
  }
}

export function formFormToApiBody(values: FormFormValues): Record<string, unknown> {
  return {
    name: values.name.trim(),
    slug: values.slug.trim(),
    description: values.description?.trim() || null,
    is_active: values.is_active,
    fields: values.fields.map((field, index) => ({
      name: field.name.trim(),
      label: field.label.trim(),
      type: field.type,
      required: field.required,
      sort_order: index,
    })),
  }
}

export const defaultContactFields: FormFieldFormValues[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'email', label: 'Email', type: 'email', required: true },
  { name: 'message', label: 'Message', type: 'textarea', required: true },
]
