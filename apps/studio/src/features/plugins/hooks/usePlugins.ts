import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  approvePluginCapability,
  disablePlugin,
  discoverPlugins,
  enablePlugin,
  installPlugin,
  listPlugins,
  uninstallPlugin,
} from '../api/pluginsApi'

export function usePlugins() {
  return useQuery({
    queryKey: ['plugins'],
    queryFn: listPlugins,
  })
}

export function useDiscoveredPlugins(enabled: boolean) {
  return useQuery({
    queryKey: ['plugins', 'discover'],
    queryFn: discoverPlugins,
    enabled,
  })
}

export function usePluginActions() {
  const queryClient = useQueryClient()

  async function refresh() {
    await queryClient.invalidateQueries({ queryKey: ['plugins'] })
    await queryClient.invalidateQueries({ queryKey: ['plugins', 'discover'] })
  }

  const install = useMutation({
    mutationFn: installPlugin,
    onSuccess: refresh,
  })

  const enable = useMutation({
    mutationFn: enablePlugin,
    onSuccess: refresh,
  })

  const disable = useMutation({
    mutationFn: disablePlugin,
    onSuccess: refresh,
  })

  const uninstall = useMutation({
    mutationFn: uninstallPlugin,
    onSuccess: refresh,
  })

  const approveCapability = useMutation({
    mutationFn: ({ pluginId, capability }: { pluginId: string; capability: string }) =>
      approvePluginCapability(pluginId, capability),
    onSuccess: refresh,
  })

  return { install, enable, disable, uninstall, approveCapability }
}
