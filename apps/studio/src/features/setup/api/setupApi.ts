import { z } from 'zod'
import { apiGet, apiRequest } from '../../../shared/api/client'

const setupToken = import.meta.env.VITE_LUMA_SETUP_TOKEN ?? ''

function setupHeaders(): Record<string, string> {
  if (setupToken === '') {
    return {}
  }

  return {
    'X-Luma-Setup-Token': setupToken,
  }
}

const setupStatusSchema = z.object({
  installed: z.boolean(),
  version: z.string(),
})

const requirementCheckSchema = z.object({
  id: z.string(),
  label: z.string(),
  status: z.enum(['passed', 'warning', 'failed']),
  message: z.string(),
})

const setupRequirementsSchema = z.object({
  passed: z.boolean(),
  checks: z.array(requirementCheckSchema),
})

const setupLogSchema = z.object({
  id: z.number(),
  step: z.string(),
  status: z.string(),
  message: z.string(),
  created_at: z.string().nullable(),
})

const setupLogsSchema = z.object({
  logs: z.array(setupLogSchema),
})

const okSchema = z.object({
  ok: z.boolean(),
})

const finishSchema = z.object({
  ok: z.boolean(),
  redirect: z.string(),
})

const healthSchema = z.object({
  status: z.string(),
  service: z.string(),
  version: z.string(),
})

export type SetupRequirementCheck = z.infer<typeof requirementCheckSchema>
export type SetupLogEntry = z.infer<typeof setupLogSchema>

export async function fetchSetupStatus(): Promise<{ installed: boolean; version: string }> {
  try {
    return await apiGet('/api/v1/setup/status', setupStatusSchema)
  } catch {
    const health = await apiGet('/api/v1/health', healthSchema)

    return {
      installed: false,
      version: health.version,
    }
  }
}

export async function fetchSetupRequirements(): Promise<{
  passed: boolean
  checks: SetupRequirementCheck[]
}> {
  // Public endpoint — readable before install without LUMA_SETUP_TOKEN / VITE_LUMA_SETUP_TOKEN.
  return apiGet('/api/v1/system/requirements', setupRequirementsSchema)
}

export async function fetchSetupLogs(): Promise<{ logs: SetupLogEntry[] }> {
  return apiGet('/api/v1/setup/logs', setupLogsSchema, { headers: setupHeaders() })
}

export async function testSetupDatabase(payload: Record<string, unknown>): Promise<{ ok: boolean }> {
  return apiRequest({
    path: '/api/v1/setup/database/test',
    method: 'POST',
    body: payload,
    schema: okSchema,
    headers: setupHeaders(),
  })
}

export async function saveSetupDatabase(payload: Record<string, unknown>): Promise<{ ok: boolean }> {
  return apiRequest({
    path: '/api/v1/setup/database',
    method: 'POST',
    body: payload,
    schema: okSchema,
    headers: setupHeaders(),
  })
}

export async function finishSetup(payload: Record<string, unknown>): Promise<{ ok: boolean; redirect: string }> {
  return apiRequest({
    path: '/api/v1/setup/finish',
    method: 'POST',
    body: payload,
    schema: finishSchema,
    headers: setupHeaders(),
  })
}
