import { z } from 'zod'
import { apiGet, apiRequest } from '../../../shared/api/client'

const versionSchema = z.object({
  data: z.object({
    version: z.string(),
    flavor: z.string().nullable(),
    build: z.string().nullable(),
    installed: z.boolean(),
    manifest_path: z.string().nullable(),
  }),
})

const updateCheckSchema = z.object({
  data: z.object({
    update_available: z.boolean(),
    current: z.string(),
    latest: z.string(),
    channel: z.string(),
  }),
})

const updateRunSchema = z.object({
  ok: z.boolean(),
  data: z.object({
    migrations: z.string(),
  }),
})

export async function fetchSystemVersion() {
  const response = await apiGet('/api/v1/system/version', versionSchema)
  return response.data
}

export async function fetchUpdateCheck() {
  const response = await apiGet('/api/v1/system/update/check', updateCheckSchema, { auth: true })
  return response.data
}

export async function runSystemUpdate() {
  return apiRequest({
    path: '/api/v1/system/update/run',
    method: 'POST',
    body: {},
    schema: updateRunSchema,
    auth: true,
  })
}
