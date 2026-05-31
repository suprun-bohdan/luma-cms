import { z } from 'zod'
import {
  apiGetList,
  apiGetWrapped,
  apiRequest,
  wrappedSchema,
} from '../../../shared/api/client'
import { fieldSchema, type Field, type FieldFormValues } from '../schemas/field'

export async function listFields(collectionSlug: string): Promise<Field[]> {
  return apiGetList(
    `/api/v1/collections/${collectionSlug}/fields`,
    fieldSchema,
    { auth: true },
  )
}

export async function getField(collectionSlug: string, fieldSlug: string): Promise<Field> {
  return apiGetWrapped(
    `/api/v1/collections/${collectionSlug}/fields/${fieldSlug}`,
    fieldSchema,
    { auth: true },
  )
}

export async function createField(
  collectionSlug: string,
  body: FieldFormValues,
): Promise<Field> {
  const response = await apiRequest({
    method: 'POST',
    path: `/api/v1/collections/${collectionSlug}/fields`,
    body,
    schema: wrappedSchema(fieldSchema),
    auth: true,
  })

  return response.data
}

export async function updateField(
  collectionSlug: string,
  fieldSlug: string,
  body: FieldFormValues,
): Promise<Field> {
  const response = await apiRequest({
    method: 'PUT',
    path: `/api/v1/collections/${collectionSlug}/fields/${fieldSlug}`,
    body,
    schema: wrappedSchema(fieldSchema),
    auth: true,
  })

  return response.data
}

export async function deleteField(collectionSlug: string, fieldSlug: string): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/collections/${collectionSlug}/fields/${fieldSlug}`,
    schema: z.null(),
    auth: true,
  })
}
