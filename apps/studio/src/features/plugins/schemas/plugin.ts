import { z } from 'zod'

export const pluginCapabilitySchema = z.object({
  capability: z.string(),
  granted: z.boolean(),
  is_dangerous: z.boolean().optional(),
  approved_at: z.string().nullable().optional(),
})

export const pluginSchema = z.object({
  plugin_id: z.string(),
  name: z.string(),
  version: z.string(),
  status: z.enum(['installed', 'enabled', 'disabled', 'failed']),
  path: z.string(),
  last_error: z.string().nullable().optional(),
  installed_at: z.string().nullable().optional(),
  enabled_at: z.string().nullable().optional(),
  capabilities: z.array(pluginCapabilitySchema).optional(),
})

export const discoveredPluginSchema = z.object({
  plugin_id: z.string(),
  name: z.string(),
  version: z.string(),
  path: z.string(),
  installed: z.boolean(),
})

export type Plugin = z.infer<typeof pluginSchema>
export type DiscoveredPlugin = z.infer<typeof discoveredPluginSchema>
export type PluginCapability = z.infer<typeof pluginCapabilitySchema>

export const auditLogSchema = z.object({
  id: z.number(),
  action: z.string(),
  subject_type: z.string().nullable(),
  subject_id: z.string().nullable(),
  metadata: z.record(z.string(), z.unknown()).nullable(),
  actor: z
    .object({
      id: z.number(),
      name: z.string(),
      email: z.string(),
    })
    .nullable()
    .optional(),
  created_at: z.string().nullable().optional(),
})

export type AuditLog = z.infer<typeof auditLogSchema>
