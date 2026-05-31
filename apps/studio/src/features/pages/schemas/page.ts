import { z } from 'zod'

export const pageStatusSchema = z.enum(['draft', 'published', 'archived'])

export type PageStatus = z.infer<typeof pageStatusSchema>

export const blockSchema = z.object({
  id: z.string().min(1),
  type: z.enum(['hero', 'rich_text', 'cta', 'contact_form', 'feature_grid', 'faq']),
  variant: z.string().optional(),
  props: z.record(z.string(), z.unknown()),
})

export const pageContentSchema = z.object({
  blocks: z.array(blockSchema),
})

export const pageSeoSchema = z.object({
  title: z.string().max(70).optional(),
  description: z.string().max(160).optional(),
  og_image: z.string().uuid().optional().nullable(),
})

export const pageSchema = z.object({
  id: z.number(),
  title: z.string(),
  slug: z.string(),
  status: pageStatusSchema,
  template: z.string(),
  content: pageContentSchema.nullable(),
  seo: pageSeoSchema.nullable(),
  published_at: z.string().nullable(),
  created_by: z.number().nullable(),
  updated_by: z.number().nullable(),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Page = z.infer<typeof pageSchema>
export type PageContent = z.infer<typeof pageContentSchema>
export type PageSeo = z.infer<typeof pageSeoSchema>

export const pageFormSchema = z.object({
  title: z.string().min(1, 'Title is required'),
  slug: z.string().min(1, 'Slug is required').regex(/^[a-z0-9-]+$/, 'Use lowercase letters, numbers, and hyphens'),
  template: z.string().optional(),
  contentJson: z.string().min(1, 'Content JSON is required'),
  seoTitle: z.string().max(70).optional(),
  seoDescription: z.string().max(160).optional(),
  seoOgImage: z.string().uuid().optional().or(z.literal('')),
})

export type PageFormValues = z.infer<typeof pageFormSchema>

export function pageFormToApiBody(values: PageFormValues): {
  title: string
  slug: string
  template?: string
  content: PageContent
  seo?: PageSeo | null
} {
  let content: PageContent

  try {
    const parsed = JSON.parse(values.contentJson) as unknown
    content = pageContentSchema.parse(parsed)
  } catch {
    throw new Error('Content must be valid JSON with a blocks array.')
  }

  const seo: PageSeo = {}
  if (values.seoTitle) {
    seo.title = values.seoTitle
  }
  if (values.seoDescription) {
    seo.description = values.seoDescription
  }
  if (values.seoOgImage) {
    seo.og_image = values.seoOgImage
  }

  return {
    title: values.title,
    slug: values.slug,
    template: values.template || 'default-page',
    content,
    seo: Object.keys(seo).length > 0 ? seo : null,
  }
}

export function pageToFormValues(page: Page): PageFormValues {
  return {
    title: page.title,
    slug: page.slug,
    template: page.template,
    contentJson: JSON.stringify(page.content ?? { blocks: [] }, null, 2),
    seoTitle: page.seo?.title ?? '',
    seoDescription: page.seo?.description ?? '',
    seoOgImage: page.seo?.og_image ?? '',
  }
}

export const defaultContentJson = JSON.stringify(
  {
    blocks: [
      {
        id: 'block-1',
        type: 'rich_text',
        props: { body: 'Page content goes here.' },
      },
    ],
  },
  null,
  2,
)
