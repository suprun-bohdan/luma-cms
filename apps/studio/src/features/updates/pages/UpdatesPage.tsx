import { Link } from 'react-router-dom'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { useRunSystemUpdate, useSystemVersion, useUpdateCheck } from '../hooks/useSystemUpdate'

export function UpdatesPage() {
  const versionQuery = useSystemVersion()
  const updateCheckQuery = useUpdateCheck()
  const runUpdateMutation = useRunSystemUpdate()

  if (versionQuery.isLoading || updateCheckQuery.isLoading) {
    return <LoadingState message="Loading update status…" />
  }

  const version = versionQuery.data
  const updateCheck = updateCheckQuery.data

  return (
    <>
      <PageHeader
        title="Updates"
        description="Apply database migrations after replacing release files on shared hosting."
      />

      {runUpdateMutation.isError && <ErrorAlert message={runUpdateMutation.error.message} />}
      {runUpdateMutation.isSuccess && (
        <Card className="mb-4 border-emerald-200 bg-emerald-50 text-sm text-emerald-900">
          Update completed. Restart queue workers if your host runs them separately.
        </Card>
      )}

      <div className="grid gap-4 lg:grid-cols-2">
        <Card className="space-y-3">
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Current release</p>
          <div className="flex items-center gap-2">
            <p className="text-2xl font-semibold text-slate-900">v{version?.version}</p>
            {version?.flavor && <Badge tone="muted">{version.flavor}</Badge>}
          </div>
          {version?.build && <p className="text-sm text-slate-600">Build {version.build}</p>}
          <p className="text-sm text-slate-600">
            Installed: {version?.installed ? 'yes' : 'no'}
          </p>
        </Card>

        <Card className="space-y-3">
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Update check</p>
          <div className="flex items-center gap-2">
            <Badge tone={updateCheck?.update_available ? 'warning' : 'success'}>
              {updateCheck?.update_available ? 'Update available' : 'Up to date'}
            </Badge>
            <span className="text-sm text-slate-600">Channel: {updateCheck?.channel}</span>
          </div>
          <p className="text-sm text-slate-600">
            Manifest latest: v{updateCheck?.latest}
          </p>
        </Card>
      </div>

      <Card className="mt-4 space-y-4">
        <p className="text-sm text-slate-700">
          Shared hosting flow: upload and extract the new <code>luma-cms-*-shared.zip</code>, replace{' '}
          <code>apps/api/app</code>, <code>vendor</code>, and <code>apps/studio/dist</code> via FTP while keeping{' '}
          <code>.env</code> and <code>storage/</code>. Then run the database update below.
        </p>
        <div className="flex flex-wrap gap-2">
          <Button
            onClick={() => runUpdateMutation.mutate()}
            disabled={runUpdateMutation.isPending || !version?.installed}
          >
            Run database update
          </Button>
          <Link to="/settings">
            <Button variant="secondary">Back to settings</Button>
          </Link>
        </div>
      </Card>
    </>
  )
}
