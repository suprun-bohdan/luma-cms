import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ApiError } from '../../../shared/api/client'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ConfirmDialog } from '../../../shared/components/ConfirmDialog'
import { DangerNotice } from '../../../shared/components/DangerNotice'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { HelpText } from '../../../shared/components/HelpText'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { PermissionNotice } from '../../../shared/components/PermissionNotice'
import { SuccessAlert } from '../../../shared/components/SuccessAlert'
import { useAuth } from '../../../shared/auth/useAuth'
import { isOwner } from '../../../shared/utils/format'
import { useRunSystemUpdate, useSystemVersion, useUpdateCheck } from '../hooks/useSystemUpdate'

export function UpdatesPage() {
  const { user } = useAuth()
  const owner = isOwner(user)
  const [confirmOpen, setConfirmOpen] = useState(false)
  const versionQuery = useSystemVersion()
  const updateCheckQuery = useUpdateCheck()
  const runUpdateMutation = useRunSystemUpdate()

  if (versionQuery.isLoading || updateCheckQuery.isLoading) {
    return <LoadingState message="Loading update status…" />
  }

  const version = versionQuery.data
  const updateCheck = updateCheckQuery.data
  const runErrorMessage =
    runUpdateMutation.error instanceof ApiError && runUpdateMutation.error.status === 403
      ? 'Owner permission required to run system updates.'
      : runUpdateMutation.error?.message

  return (
    <>
      <PageHeader
        title="Updates"
        description="Apply database migrations after you replace release files on your server."
      />

      {!owner && (
        <div className="mb-4">
          <PermissionNotice
            message="Owner permission is required to run system updates. Ask your site owner to perform this step, or use SSH with php artisan luma:update if you have server access."
          />
        </div>
      )}

      {runUpdateMutation.isError && runErrorMessage && <ErrorAlert message={runErrorMessage} />}
      {runUpdateMutation.isSuccess && (
        <div className="mb-4">
          <SuccessAlert message="Update completed. Restart queue workers if your host runs them separately." />
        </div>
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
        <DangerNotice title="Back up before updating">
          Back up your database and <code>storage/</code> directory before running a database update.
          Keep a copy of <code>.env</code> outside the web root.
        </DangerNotice>

        <HelpText>
          Shared hosting: upload and extract the new <code>luma-cms-*-shared.zip</code>, replace{' '}
          <code>apps/api/app</code>, <code>vendor</code>, and <code>apps/studio/dist</code> via FTP
          while keeping <code>.env</code> and <code>storage/</code>. Then run the database update
          below. This is not an automatic app-store update — you upload files first, then migrate.
        </HelpText>

        <HelpText>
          SSH or cron: after replacing files, you can run{' '}
          <code className="font-mono text-xs">php artisan luma:update</code> instead of the button
          below. Both paths run migrations only; neither downloads a release for you.
        </HelpText>

        <div className="flex flex-wrap gap-2">
          <Button
            onClick={() => setConfirmOpen(true)}
            disabled={runUpdateMutation.isPending || !version?.installed || !owner}
          >
            Run database update
          </Button>
          <Link to="/settings">
            <Button variant="secondary">Back to settings</Button>
          </Link>
        </div>
      </Card>

      <ConfirmDialog
        open={confirmOpen}
        title="Run database update?"
        description="Confirm you have backed up your database and storage folder. This applies pending migrations and cannot be undone from Studio."
        confirmLabel="Run update"
        loading={runUpdateMutation.isPending}
        onCancel={() => setConfirmOpen(false)}
        onConfirm={() => {
          runUpdateMutation.mutate(undefined, {
            onSettled: () => setConfirmOpen(false),
          })
        }}
      />
    </>
  )
}
