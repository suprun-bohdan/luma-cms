import { z } from 'zod'
import { apiGetList, apiRequest } from '../../../shared/api/client'
import { pageSchema, type Page, type PageContent, type PageSeo, type PageStatus } from '../schemas/page'

export async function listPages(status?: PageStatus): Promise<Page[]> {
  const query = status ? `?status=${status}` : ''

  return apiGetList(`/api/v1/pages${query}`, pageSchema, { auth: true })
}

export async function getPage(slug: string): Promise<Page> {
  return apiRequest({
    method: 'GET',
    path: `/api/v1/pages/${slug}`,
    schema: pageSchema,
    auth: true,
  })
}

export async function createPage(body: Record<string, unknown>): Promise<Page> {
  return apiRequest({
    method: 'POST',
    path: '/api/v1/pages',
    body,
    schema: pageSchema,
    auth: true,
  })
}

export async function updatePage(slug: string, body: Record<string, unknown>): Promise<Page> {
  return apiRequest({
    method: 'PUT',
    path: `/api/v1/pages/${slug}`,
    body,
    schema: pageSchema,
    auth: true,
  })
}

export async function deletePage(slug: string): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/pages/${slug}`,
    schema: z.null(),
    auth: true,
  })
}

export async function publishPage(slug: string): Promise<Page> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/pages/${slug}/publish`,
    schema: pageSchema,
    auth: true,
  })
}

export async function unpublishPage(slug: string): Promise<Page> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/pages/${slug}/unpublish`,
    schema: pageSchema,
    auth: true,
  })
}

const previewHtmlSchema = z.object({
  html: z.string(),
})

export async function previewPageHtml(
  slug: string,
  body: {
    title: string
    content: PageContent
    seo: PageSeo | null
  },
): Promise<{ html: string }> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/pages/${slug}/preview-html`,
    body: {
      title: body.title,
      content: body.content,
      seo: body.seo,
    },
    schema: previewHtmlSchema,
    auth: true,
  })
}

export async function previewDraftPageHtml(body: {
  slug: string
  title: string
  content: PageContent
  seo: PageSeo | null
}): Promise<{ html: string }> {
  return apiRequest({
    method: 'POST',
    path: '/api/v1/pages/preview-html',
    body: {
      slug: body.slug,
      title: body.title,
      content: body.content,
      seo: body.seo,
    },
    schema: previewHtmlSchema,
    auth: true,
  })
}
