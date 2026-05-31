import { z } from 'zod'
import { apiGetList, apiRequest } from '../../../shared/api/client'
import { redirectSchema, type Redirect } from '../schemas/redirect'

export async function listRedirects(): Promise<Redirect[]> {
  return apiGetList('/api/v1/redirects', redirectSchema, { auth: true })
}

export async function getRedirect(id: number): Promise<Redirect> {
  return apiRequest({
    method: 'GET',
    path: `/api/v1/redirects/${id}`,
    schema: redirectSchema,
    auth: true,
  })
}

export async function createRedirect(body: Record<string, unknown>): Promise<Redirect> {
  return apiRequest({
    method: 'POST',
    path: '/api/v1/redirects',
    body,
    schema: redirectSchema,
    auth: true,
  })
}

export async function updateRedirect(
  id: number,
  body: Record<string, unknown>,
): Promise<Redirect> {
  return apiRequest({
    method: 'PUT',
    path: `/api/v1/redirects/${id}`,
    body,
    schema: redirectSchema,
    auth: true,
  })
}

export async function deleteRedirect(id: number): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/redirects/${id}`,
    schema: z.null(),
    auth: true,
  })
}
