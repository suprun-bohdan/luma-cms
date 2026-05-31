import { z } from 'zod'
import { apiGetList, apiRequest } from '../../../shared/api/client'
import { entrySchema, type Entry, type EntryFormValues, type EntryStatus } from '../schemas/entry'

export async function listEntries(
  collectionSlug: string,
  status?: EntryStatus,
): Promise<Entry[]> {
  const query = status ? `?status=${status}` : ''

  return apiGetList(
    `/api/v1/collections/${collectionSlug}/entries${query}`,
    entrySchema,
    { auth: true },
  )
}

export async function getEntry(id: number): Promise<Entry> {
  return apiRequest({
    method: 'GET',
    path: `/api/v1/entries/${id}`,
    schema: entrySchema,
    auth: true,
  })
}

export async function createEntry(
  collectionSlug: string,
  body: EntryFormValues,
): Promise<Entry> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/collections/${collectionSlug}/entries`,
    body,
    schema: entrySchema,
    auth: true,
  })
}

export async function updateEntry(id: number, body: EntryFormValues): Promise<Entry> {
  return apiRequest({
    method: 'PUT',
    path: `/api/v1/entries/${id}`,
    body,
    schema: entrySchema,
    auth: true,
  })
}

export async function deleteEntry(id: number): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/entries/${id}`,
    schema: z.null(),
    auth: true,
  })
}

export async function publishEntry(id: number): Promise<Entry> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/entries/${id}/publish`,
    schema: entrySchema,
    auth: true,
  })
}

export async function unpublishEntry(id: number): Promise<Entry> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/entries/${id}/unpublish`,
    schema: entrySchema,
    auth: true,
  })
}

export async function getPublicEntry(id: number): Promise<Entry> {
  return apiRequest({
    method: 'GET',
    path: `/api/v1/public/entries/${id}`,
    schema: entrySchema,
  })
}
