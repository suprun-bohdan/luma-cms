import { z } from 'zod'

export const entryStatusSchema = z.enum(['draft', 'published', 'archived'])

export type EntryStatus = z.infer<typeof entryStatusSchema>

export const entrySchema = z.object({
  id: z.number(),
  collection_id: z.number(),
  status: entryStatusSchema,
  data: z.record(z.string(), z.unknown()),
  created_by: z.number().nullable(),
  updated_by: z.number().nullable(),
  published_at: z.string().nullable(),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Entry = z.infer<typeof entrySchema>

export const entryFormSchema = z.object({
  data: z.record(z.string(), z.unknown()),
})

export type EntryFormValues = z.infer<typeof entryFormSchema>
