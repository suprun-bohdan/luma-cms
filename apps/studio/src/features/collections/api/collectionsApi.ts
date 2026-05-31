import { z } from 'zod'
import {
  apiGetList,
  apiGetWrapped,
  apiRequest,
  wrappedSchema,
} from '../../../shared/api/client'
import {
  collectionSchema,
  type Collection,
  type CollectionFormValues,
} from '../schemas/collection'

export async function listCollections(): Promise<Collection[]> {
  return apiGetList('/api/v1/collections', collectionSchema, { auth: true })
}

export async function getCollection(slug: string): Promise<Collection> {
  return apiGetWrapped(`/api/v1/collections/${slug}`, collectionSchema, { auth: true })
}

export async function createCollection(body: CollectionFormValues): Promise<Collection> {
  const response = await apiRequest({
    method: 'POST',
    path: '/api/v1/collections',
    body,
    schema: wrappedSchema(collectionSchema),
    auth: true,
  })

  return response.data
}

export async function updateCollection(
  slug: string,
  body: CollectionFormValues,
): Promise<Collection> {
  const response = await apiRequest({
    method: 'PUT',
    path: `/api/v1/collections/${slug}`,
    body,
    schema: wrappedSchema(collectionSchema),
    auth: true,
  })

  return response.data
}

export async function deleteCollection(slug: string): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/collections/${slug}`,
    schema: z.null(),
    auth: true,
  })
}
