import { useHealth } from '../hooks/useHealth'

export function HomePage() {
  const { data, error, isLoading, isError } = useHealth()

  return (
    <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 className="text-lg font-medium">API connection</h2>
      <p className="mt-2 text-sm text-slate-600">
        Checks the Laravel backend via <code className="text-xs">/api/v1/health</code>.
      </p>

      {isLoading && (
        <p className="mt-4 text-sm text-slate-500">Connecting to API…</p>
      )}

      {isError && (
        <p className="mt-4 text-sm text-red-600">
          {error instanceof Error ? error.message : 'API unavailable'}
        </p>
      )}

      {data && (
        <dl className="mt-4 grid gap-2 text-sm">
          <div className="flex gap-2">
            <dt className="w-24 font-medium text-slate-500">Status</dt>
            <dd>{data.status}</dd>
          </div>
          <div className="flex gap-2">
            <dt className="w-24 font-medium text-slate-500">Service</dt>
            <dd>{data.service}</dd>
          </div>
          <div className="flex gap-2">
            <dt className="w-24 font-medium text-slate-500">Version</dt>
            <dd>{data.version}</dd>
          </div>
        </dl>
      )}
    </section>
  )
}
