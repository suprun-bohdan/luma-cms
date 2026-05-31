import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { ApiError } from '../../../shared/api/client'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
import { ListPage } from '../../../shared/layout'
import { useDiscoveredPlugins, usePluginActions, usePlugins } from '../hooks/usePlugins'
import type { Plugin, PluginCapability } from '../schemas/plugin'

function CapabilityRow({
  pluginId,
  capability,
  onApproved,
}: {
  pluginId: string
  capability: PluginCapability
  onApproved: () => void
}) {
  const actions = usePluginActions()
  const [approvedMessage, setApprovedMessage] = useState<string | null>(null)

  return (
    <TableRow>
      <TableCell className="font-mono text-xs">{capability.capability}</TableCell>
      <TableCell>{capability.granted ? 'Yes' : 'Pending'}</TableCell>
      <TableCell>
        {capability.is_dangerous ? <Badge tone="warning">Dangerous</Badge> : '—'}
      </TableCell>
      <TableCell className="text-right">
        {!capability.granted && (
          <Button
            variant="secondary"
            disabled={actions.approveCapability.isPending}
            onClick={() => {
              actions.approveCapability.mutate(
                { pluginId, capability: capability.capability },
                {
                  onSuccess: () => {
                    setApprovedMessage(`Approved ${capability.capability}`)
                    onApproved()
                  },
                },
              )
            }}
          >
            Approve
          </Button>
        )}
        {approvedMessage && <p className="mt-1 text-xs text-emerald-700">{approvedMessage}</p>}
      </TableCell>
    </TableRow>
  )
}

function PluginCapabilitiesPanel({ plugin }: { plugin: Plugin }) {
  const capabilities = plugin.capabilities ?? []

  if (capabilities.length === 0) {
    return <p className="text-sm text-slate-500">No capabilities declared in manifest.</p>
  }

  return (
    <Table>
      <TableHead>
        <TableRow>
          <TableHeaderCell>Capability</TableHeaderCell>
          <TableHeaderCell>Granted</TableHeaderCell>
          <TableHeaderCell>Dangerous</TableHeaderCell>
          <TableHeaderCell />
        </TableRow>
      </TableHead>
      <TableBody>
        {capabilities.map((capability) => (
          <CapabilityRow
            key={capability.capability}
            pluginId={plugin.plugin_id}
            capability={capability}
            onApproved={() => undefined}
          />
        ))}
      </TableBody>
    </Table>
  )
}

export function PluginsListPage() {
  const [showDiscover, setShowDiscover] = useState(false)
  const [expandedPluginId, setExpandedPluginId] = useState<string | null>(null)
  const [enableHintPluginId, setEnableHintPluginId] = useState<string | null>(null)
  const capabilitiesRef = useRef<HTMLDivElement | null>(null)
  const pluginsQuery = usePlugins()
  const discoverQuery = useDiscoveredPlugins(showDiscover)
  const actions = usePluginActions()

  useEffect(() => {
    if (enableHintPluginId && expandedPluginId === enableHintPluginId) {
      capabilitiesRef.current?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    }
  }, [enableHintPluginId, expandedPluginId])

  const mutationError =
    actions.install.error ??
    actions.enable.error ??
    actions.disable.error ??
    actions.uninstall.error ??
    actions.approveCapability.error

  function handleEnable(pluginId: string) {
    setEnableHintPluginId(null)
    actions.enable.mutate(pluginId, {
      onError: (error) => {
        if (error instanceof ApiError && error.message.includes('Dangerous capabilities')) {
          setEnableHintPluginId(pluginId)
          setExpandedPluginId(pluginId)
        }
      },
    })
  }

  return (
    <ListPage
      title="Plugins"
      description="Install and manage internal plugins. Dangerous capabilities require explicit approval."
      actions={
        <div className="flex flex-wrap gap-2">
          <Link to="/plugins/audit-logs">
            <Button variant="secondary">Audit log</Button>
          </Link>
          <Button variant="secondary" onClick={() => setShowDiscover((current) => !current)}>
            {showDiscover ? 'Hide discover' : 'Discover plugins'}
          </Button>
        </div>
      }
      loading={pluginsQuery.isLoading}
      loadingMessage="Loading plugins…"
      error={
        pluginsQuery.isError ? (
          <ErrorAlert
            message={
              pluginsQuery.error instanceof Error
                ? pluginsQuery.error.message
                : 'Failed to load plugins'
            }
          />
        ) : undefined
      }
    >
      {mutationError instanceof Error && (
        <div className="mb-4">
          <ErrorAlert message={mutationError.message} />
        </div>
      )}

      {enableHintPluginId && (
        <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
          Approve pending dangerous capabilities below, then enable the plugin again.
        </div>
      )}

      {showDiscover && (
        <section className="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-4">
          <h2 className="text-sm font-semibold text-slate-900">Discovered on disk</h2>
          {discoverQuery.isLoading && <p className="mt-2 text-sm text-slate-500">Scanning…</p>}
          {discoverQuery.data && discoverQuery.data.length === 0 && (
            <p className="mt-2 text-sm text-slate-500">No plugin manifests found.</p>
          )}
          {discoverQuery.data && discoverQuery.data.length > 0 && (
            <ul className="mt-3 space-y-2">
              {discoverQuery.data.map((plugin) => (
                <li
                  key={plugin.plugin_id}
                  className="flex flex-wrap items-center justify-between gap-3 rounded-md border border-slate-200 bg-white px-3 py-2"
                >
                  <div>
                    <p className="text-sm font-medium text-slate-900">{plugin.name}</p>
                    <p className="font-mono text-xs text-slate-500">
                      {plugin.plugin_id} · v{plugin.version}
                    </p>
                  </div>
                  <Button
                    disabled={plugin.installed || actions.install.isPending}
                    onClick={() => actions.install.mutate(plugin.plugin_id)}
                  >
                    {plugin.installed ? 'Installed' : 'Install'}
                  </Button>
                </li>
              ))}
            </ul>
          )}
        </section>
      )}

      {pluginsQuery.data && pluginsQuery.data.length > 0 && (
        <div className="space-y-4">
          {pluginsQuery.data.map((plugin) => (
            <section
              key={plugin.plugin_id}
              ref={expandedPluginId === plugin.plugin_id ? capabilitiesRef : undefined}
              className="rounded-lg border border-slate-200 bg-white"
            >
              <div className="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                <div>
                  <p className="font-medium text-slate-900">{plugin.name}</p>
                  <p className="font-mono text-xs text-slate-500">
                    {plugin.plugin_id} · v{plugin.version} · {plugin.status}
                  </p>
                </div>
                <div className="flex flex-wrap gap-2">
                  <Button
                    variant="ghost"
                    onClick={() =>
                      setExpandedPluginId((current) =>
                        current === plugin.plugin_id ? null : plugin.plugin_id,
                      )
                    }
                  >
                    {expandedPluginId === plugin.plugin_id ? 'Hide capabilities' : 'Capabilities'}
                  </Button>
                  {plugin.status !== 'enabled' ? (
                    <Button
                      variant="secondary"
                      disabled={actions.enable.isPending}
                      onClick={() => handleEnable(plugin.plugin_id)}
                    >
                      Enable
                    </Button>
                  ) : (
                    <Button
                      variant="secondary"
                      disabled={actions.disable.isPending}
                      onClick={() => actions.disable.mutate(plugin.plugin_id)}
                    >
                      Disable
                    </Button>
                  )}
                  <Button
                    variant="ghost"
                    disabled={actions.uninstall.isPending}
                    onClick={() => actions.uninstall.mutate(plugin.plugin_id)}
                  >
                    Uninstall
                  </Button>
                </div>
              </div>

              {expandedPluginId === plugin.plugin_id && (
                <div className="border-t border-slate-200 px-4 py-3">
                  <PluginCapabilitiesPanel plugin={plugin} />
                </div>
              )}
            </section>
          ))}
        </div>
      )}

      {pluginsQuery.data?.length === 0 && (
        <p className="text-sm text-slate-500">
          No plugins installed. Use Discover to scan the plugins directory.
        </p>
      )}
    </ListPage>
  )
}
