import { z } from 'zod'

export const collectionSchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string(),
  description: z.string().nullable(),
  config: z.record(z.string(), z.unknown()).nullable(),
  schema_version: z.number(),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Collection = z.infer<typeof collectionSchema>

export const collectionFormSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255),
  slug: z
    .string()
    .min(1, 'Slug is required')
    .max(255)
    .regex(/^[a-z0-9-]+$/, 'Use lowercase letters, numbers, and dashes only'),
  description: z.string().optional(),
})

export type CollectionFormValues = z.infer<typeof collectionFormSchema>
