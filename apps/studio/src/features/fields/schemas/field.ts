import { z } from 'zod'

export const fieldTypeSchema = z.enum([
  'text',
  'textarea',
  'number',
  'boolean',
  'datetime',
  'json',
])

export type FieldType = z.infer<typeof fieldTypeSchema>

export const fieldSchema = z.object({
  id: z.number(),
  collection_id: z.number(),
  name: z.string(),
  slug: z.string(),
  type: fieldTypeSchema,
  config: z.record(z.string(), z.unknown()).nullable(),
  sort_order: z.number(),
  required: z.boolean(),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Field = z.infer<typeof fieldSchema>

export const fieldFormSchema = z.object({
  name: z.string().min(1).max(255),
  slug: z
    .string()
    .min(1)
    .max(255)
    .regex(/^[a-z0-9-]+$/),
  type: fieldTypeSchema,
  sort_order: z.coerce.number().min(0).optional(),
  required: z.boolean().optional(),
  config: z.record(z.string(), z.unknown()).nullable().optional(),
})

export type FieldFormValues = z.infer<typeof fieldFormSchema>

export const fieldTypeLabels: Record<FieldType, string> = {
  text: 'Text',
  textarea: 'Textarea',
  number: 'Number',
  boolean: 'Boolean',
  datetime: 'Datetime',
  json: 'JSON',
}
