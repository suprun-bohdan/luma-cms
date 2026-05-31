import { z } from 'zod'
import { apiGetList, apiRequest } from '../../../shared/api/client'
import { discoveredPluginSchema, pluginSchema, type DiscoveredPlugin, type Plugin } from '../schemas/plugin'

export async function listPlugins(): Promise<Plugin[]> {
  return apiGetList('/api/v1/plugins', pluginSchema, { auth: true })
}

export async function discoverPlugins(): Promise<DiscoveredPlugin[]> {
  const response = await apiRequest({
    method: 'GET',
    path: '/api/v1/plugins/discover',
    schema: z.object({ data: z.array(discoveredPluginSchema) }),
    auth: true,
  })

  return response.data
}

export async function installPlugin(pluginId: string): Promise<Plugin> {
  return apiRequest({
    method: 'POST',
    path: '/api/v1/plugins/install',
    body: { plugin_id: pluginId },
    schema: pluginSchema,
    auth: true,
  })
}

export async function enablePlugin(pluginId: string): Promise<Plugin> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/plugins/${pluginId}/enable`,
    schema: pluginSchema,
    auth: true,
  })
}

export async function disablePlugin(pluginId: string): Promise<Plugin> {
  return apiRequest({
    method: 'POST',
    path: `/api/v1/plugins/${pluginId}/disable`,
    schema: pluginSchema,
    auth: true,
  })
}

export async function uninstallPlugin(pluginId: string): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/plugins/${pluginId}`,
    schema: z.null(),
    auth: true,
  })
}
