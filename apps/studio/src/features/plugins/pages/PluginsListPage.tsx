import { useState } from 'react'
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

export function PluginsListPage() {
  const [showDiscover, setShowDiscover] = useState(false)
  const pluginsQuery = usePlugins()
  const discoverQuery = useDiscoveredPlugins(showDiscover)
  const actions = usePluginActions()

  const mutationError =
    actions.install.error ??
    actions.enable.error ??
    actions.disable.error ??
    actions.uninstall.error

  return (
    <ListPage
      title="Plugins"
      description="Install and manage internal plugins. Capabilities are granted per manifest."
      actions={
        <Button variant="secondary" onClick={() => setShowDiscover((current) => !current)}>
          {showDiscover ? 'Hide discover' : 'Discover plugins'}
        </Button>
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
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>Plugin</TableHeaderCell>
              <TableHeaderCell>Version</TableHeaderCell>
              <TableHeaderCell>Status</TableHeaderCell>
              <TableHeaderCell />
            </TableRow>
          </TableHead>
          <TableBody>
            {pluginsQuery.data.map((plugin) => (
              <TableRow key={plugin.plugin_id}>
                <TableCell>
                  <p className="font-medium text-slate-900">{plugin.name}</p>
                  <p className="font-mono text-xs text-slate-500">{plugin.plugin_id}</p>
                </TableCell>
                <TableCell>{plugin.version}</TableCell>
                <TableCell className="capitalize">{plugin.status}</TableCell>
                <TableCell className="space-x-2 text-right">
                  {plugin.status !== 'enabled' ? (
                    <Button
                      variant="secondary"
                      disabled={actions.enable.isPending}
                      onClick={() => actions.enable.mutate(plugin.plugin_id)}
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
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}

      {pluginsQuery.data?.length === 0 && (
        <p className="text-sm text-slate-500">
          No plugins installed. Use Discover to scan the plugins directory.
        </p>
      )}
    </ListPage>
  )
}
