import { Link } from 'react-router-dom'
import { Badge } from '../shared/components/Badge'
import { Button } from '../shared/components/Button'
import { Card } from '../shared/components/Card'
import { ErrorAlert } from '../shared/components/ErrorAlert'
import { LoadingState } from '../shared/components/LoadingState'
import { PageHeader } from '../shared/components/PageHeader'
import { useHealth } from '../shared/hooks/useHealth'
import { useCollections } from '../features/collections/hooks/useCollections'

export function DashboardPage() {
  const healthQuery = useHealth()
  const collectionsQuery = useCollections()

  return (
    <>
      <PageHeader
        title="Dashboard"
        description="Overview of your Luma CMS Studio workspace."
      />

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Card>
          <p className="text-xs uppercase text-slate-500">Collections</p>
          <p className="mt-2 text-3xl font-semibold">
            {collectionsQuery.isLoading ? '…' : (collectionsQuery.data?.length ?? 0)}
          </p>
          <Link to="/collections" className="mt-4 inline-block">
            <Button variant="secondary">Manage collections</Button>
          </Link>
        </Card>

        <Card>
          <p className="text-xs uppercase text-slate-500">API health</p>
          {healthQuery.isLoading && <LoadingState message="Checking API…" />}
          {healthQuery.isError && <ErrorAlert message={healthQuery.error.message} />}
          {healthQuery.data && (
            <div className="mt-2 space-y-2">
              <Badge tone={healthQuery.data.status === 'ok' ? 'success' : 'warning'}>
                {healthQuery.data.status}
              </Badge>
              <p className="text-sm text-slate-600">{healthQuery.data.service}</p>
              <p className="text-xs text-slate-500">v{healthQuery.data.version}</p>
            </div>
          )}
        </Card>

        <Card>
          <p className="text-xs uppercase text-slate-500">Quick links</p>
          <div className="mt-4 flex flex-col gap-2">
            <Link to="/collections/new">
              <Button className="w-full">New collection</Button>
            </Link>
            <Link to="/media">
              <Button variant="secondary" className="w-full">
                Media library
              </Button>
            </Link>
          </div>
        </Card>
      </div>
    </>
  )
}
