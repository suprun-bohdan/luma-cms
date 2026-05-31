import { z } from 'zod'

export const mediaVariantSchema = z.object({
  name: z.string().nullable(),
  path: z.string().nullable(),
  url: z.string().nullable(),
  width: z.number().nullable(),
  height: z.number().nullable(),
})

export const mediaSchema = z.object({
  uuid: z.string(),
  filename: z.string(),
  mime_type: z.string(),
  size: z.number(),
  width: z.number().nullable(),
  height: z.number().nullable(),
  alt_text: z.string().nullable(),
  url: z.string(),
  variants: z.array(mediaVariantSchema),
  uploaded_by: z.number().nullable(),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Media = z.infer<typeof mediaSchema>
export type MediaVariant = z.infer<typeof mediaVariantSchema>

export const updateMediaSchema = z.object({
  alt_text: z.string().max(255).nullable().optional(),
})

export type UpdateMediaValues = z.infer<typeof updateMediaSchema>
