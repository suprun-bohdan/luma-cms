import { z } from 'zod'

export const menuItemSchema = z.object({
  id: z.number().optional(),
  label: z.string().min(1),
  page_slug: z.string().optional().nullable(),
  url: z.string().optional().nullable(),
  sort_order: z.number().optional(),
  resolved_url: z.string().optional(),
})

export const menuSchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string(),
  items: z.array(menuItemSchema).optional(),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Menu = z.infer<typeof menuSchema>
export type MenuItem = z.infer<typeof menuItemSchema>

export const menuFormSchema = z.object({
  name: z.string().min(1),
  slug: z.string().min(1),
  items: z.array(
    z.object({
      label: z.string().min(1, 'Label is required'),
      page_slug: z.string().optional(),
      url: z.string().url().optional().or(z.literal('')),
      sort_order: z.number(),
    }),
  ),
})

export type MenuFormValues = z.infer<typeof menuFormSchema>
