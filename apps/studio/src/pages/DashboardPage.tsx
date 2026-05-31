import { Link } from 'react-router-dom'
import { useOnboardingJournal } from '../features/onboarding/hooks/useOnboarding'
import { useForms } from '../features/forms/hooks/useForms'
import { useMediaList } from '../features/media/hooks/useMedia'
import { usePages } from '../features/pages/hooks/usePages'
import { Badge } from '../shared/components/Badge'
import { Button } from '../shared/components/Button'
import { Card } from '../shared/components/Card'
import { EmptyState } from '../shared/components/EmptyState'
import { ErrorAlert } from '../shared/components/ErrorAlert'
import { LoadingState } from '../shared/components/LoadingState'
import { PageHeader } from '../shared/components/PageHeader'
import { useAuth } from '../shared/auth/useAuth'
import { useHealth } from '../shared/hooks/useHealth'
import { isOwner } from '../shared/utils/format'

type ActionCardProps = {
  label: string
  count: number | string
  loading: boolean
  to: string
  actionLabel: string
}

function ActionCard({ label, count, loading, to, actionLabel }: ActionCardProps) {
  return (
    <Card>
      <p className="text-xs uppercase text-slate-500">{label}</p>
      <p className="mt-2 text-3xl font-semibold">{loading ? '…' : count}</p>
      <Link to={to} className="mt-4 inline-block">
        <Button variant="secondary">{actionLabel}</Button>
      </Link>
    </Card>
  )
}

export function DashboardPage() {
  const { user } = useAuth()
  const owner = isOwner(user)
  const canViewSetupLogs = owner
  const healthQuery = useHealth()
  const pagesQuery = usePages()
  const formsQuery = useForms()
  const mediaQuery = useMediaList()
  const journalQuery = useOnboardingJournal(canViewSetupLogs)

  const pageCount = pagesQuery.data?.length ?? 0
  const formCount = formsQuery.data?.length ?? 0
  const mediaCount = mediaQuery.data?.length ?? 0
  const contentLoading = pagesQuery.isLoading || formsQuery.isLoading || mediaQuery.isLoading
  const workspaceEmpty = !contentLoading && pageCount === 0 && formCount === 0 && mediaCount === 0

  const recommendedActions = [
    pageCount === 0 && { label: 'Create your first page', to: '/pages/new' },
    formCount === 0 && { label: 'Add a contact form', to: '/forms/new' },
    mediaCount === 0 && { label: 'Upload media', to: '/media' },
  ].filter(Boolean) as Array<{ label: string; to: string }>

  return (
    <>
      <PageHeader
        title="Dashboard"
        description="Your workspace at a glance."
      />

      <Card className="mb-6">
        <h2 className="text-lg font-semibold text-slate-900">
          Welcome{user?.name ? `, ${user.name}` : ''}
        </h2>
        <p className="mt-1 text-sm text-slate-600">
          Build pages, manage media, and publish your site from Luma Studio.
        </p>
        {recommendedActions.length > 0 && (
          <div className="mt-4 flex flex-wrap gap-2">
            {recommendedActions.map((action) => (
              <Link key={action.to} to={action.to}>
                <Button>{action.label}</Button>
              </Link>
            ))}
          </div>
        )}
      </Card>

      {workspaceEmpty && (
        <div className="mb-6">
          <EmptyState
            title="Your site is ready for content"
            description="Start with a page, add images to the media library, or create a form for visitors to reach you."
            action={
              <Link to="/pages/new">
                <Button>Create first page</Button>
              </Link>
            }
          />
        </div>
      )}

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <ActionCard
          label="Pages"
          count={pageCount}
          loading={pagesQuery.isLoading}
          to="/pages"
          actionLabel="Manage pages"
        />
        <ActionCard
          label="Media"
          count={mediaCount}
          loading={mediaQuery.isLoading}
          to="/media"
          actionLabel="Open media library"
        />
        <ActionCard
          label="Forms"
          count={formCount}
          loading={formsQuery.isLoading}
          to="/forms"
          actionLabel="Manage forms"
        />
        <ActionCard
          label="Navigation"
          count="—"
          loading={false}
          to="/menus"
          actionLabel="Edit menus"
        />
        <Card>
          <p className="text-xs uppercase text-slate-500">Site status</p>
          {healthQuery.isLoading && <LoadingState message="Checking connection…" />}
          {healthQuery.isError && <ErrorAlert message={healthQuery.error.message} />}
          {healthQuery.data && (
            <div className="mt-2 space-y-2">
              {owner ? (
                <>
                  <Badge tone={healthQuery.data.status === 'ok' ? 'success' : 'warning'}>
                    {healthQuery.data.status}
                  </Badge>
                  <p className="text-sm text-slate-600">{healthQuery.data.service}</p>
                  <p className="text-xs text-slate-500">v{healthQuery.data.version}</p>
                </>
              ) : (
                <>
                  <Badge tone="success">Connected</Badge>
                  <p className="text-sm text-slate-600">Your site is connected and responding.</p>
                </>
              )}
            </div>
          )}
        </Card>
        <Card>
          <p className="text-xs uppercase text-slate-500">Settings</p>
          <p className="mt-2 text-sm text-slate-600">Site title, tagline, and release updates.</p>
          <Link to="/settings" className="mt-4 inline-block">
            <Button variant="secondary">Open settings</Button>
          </Link>
        </Card>
      </div>

      {canViewSetupLogs && (
        <Card className="mt-6">
          <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Setup history</p>
          {journalQuery.isLoading && <LoadingState message="Loading setup history…" />}
          <div className="space-y-2 text-sm">
            {!journalQuery.isLoading && (journalQuery.data ?? []).length === 0 && (
              <p className="text-slate-500">Recent setup and onboarding events appear here.</p>
            )}
            {(journalQuery.data ?? []).map((entry) => (
              <div key={entry.id} className="rounded border border-slate-200 px-3 py-2">
                <div className="flex items-center justify-between gap-2">
                  <span className="font-medium">{entry.step}</span>
                  <Badge tone="muted">{entry.status}</Badge>
                </div>
                <p className="text-slate-600">{entry.message}</p>
              </div>
            ))}
          </div>
        </Card>
      )}
    </>
  )
}
