import { z } from 'zod'
import {
  apiGetList,
  apiGetWrapped,
  apiRequest,
  apiUpload,
  wrappedSchema,
} from '../../../shared/api/client'
import { mediaSchema, type Media, type UpdateMediaValues } from '../schemas/media'

export async function listMedia(): Promise<Media[]> {
  return apiGetList('/api/v1/media', mediaSchema, { auth: true })
}

export async function getMedia(uuid: string): Promise<Media> {
  return apiGetWrapped(`/api/v1/media/${uuid}`, mediaSchema, { auth: true })
}

export async function uploadMedia(file: File, altText?: string): Promise<Media> {
  const formData = new FormData()
  formData.append('file', file)
  if (altText?.trim()) {
    formData.append('alt_text', altText.trim())
  }

  return apiUpload('/api/v1/media', formData, mediaSchema, { auth: true })
}

export async function updateMedia(uuid: string, body: UpdateMediaValues): Promise<Media> {
  const response = await apiRequest({
    method: 'PUT',
    path: `/api/v1/media/${uuid}`,
    body,
    schema: wrappedSchema(mediaSchema),
    auth: true,
  })

  return response.data
}

export async function deleteMedia(uuid: string): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/media/${uuid}`,
    schema: z.null(),
    auth: true,
  })
}

export function getMediaPreviewUrl(media: Media): string {
  const thumbnail = media.variants.find((variant) => variant.name === 'thumbnail')

  return thumbnail?.url ?? media.url
}
