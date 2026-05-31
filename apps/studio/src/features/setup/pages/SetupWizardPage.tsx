import { useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import {
  useSetupDatabaseActions,
  useSetupLogs,
  useSetupRequirements,
  useSetupStatus,
} from '../hooks/useSetup'

const steps = ['Requirements', 'Database', 'Admin', 'Finish'] as const

export function SetupWizardPage() {
  const navigate = useNavigate()
  const statusQuery = useSetupStatus()
  const [stepIndex, setStepIndex] = useState(0)
  const requirementsQuery = useSetupRequirements(stepIndex === 0)
  const logsQuery = useSetupLogs(true)
  const { testMutation, saveMutation, finishMutation } = useSetupDatabaseActions()

  const [driver, setDriver] = useState('sqlite')
  const [host, setHost] = useState('127.0.0.1')
  const [port, setPort] = useState('3306')
  const [database, setDatabase] = useState('')
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [siteTitle, setSiteTitle] = useState('My Luma Site')
  const [adminEmail, setAdminEmail] = useState('admin@example.com')
  const [adminPassword, setAdminPassword] = useState('password123')
  const [withStarterSite, setWithStarterSite] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (statusQuery.data?.installed) {
      navigate('/login', { replace: true })
    }
  }, [navigate, statusQuery.data?.installed])

  const progress = useMemo(() => Math.round(((stepIndex + 1) / steps.length) * 100), [stepIndex])

  const databasePayload = {
    driver,
    host,
    port: Number(port),
    database: driver === 'sqlite' ? database || undefined : database,
    username,
    password,
  }

  async function handleDatabaseNext() {
    setError(null)
    try {
      await testMutation.mutateAsync(databasePayload)
      await saveMutation.mutateAsync(databasePayload)
      setStepIndex(2)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Database setup failed')
    }
  }

  async function handleFinish() {
    setError(null)
    try {
      const result = await finishMutation.mutateAsync({
        site_title: siteTitle,
        admin_email: adminEmail,
        admin_password: adminPassword,
        with_starter_site: withStarterSite,
      })
      navigate(result.redirect.replace(/^\/studio/, '') || '/login', { replace: true })
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Installation failed')
    }
  }

  if (statusQuery.isLoading) {
    return <LoadingState message="Checking installation status…" />
  }

  return (
    <div className="min-h-screen bg-slate-50 px-4 py-10">
      <div className="mx-auto max-w-3xl space-y-6">
        <PageHeader
          title="Install Luma CMS"
          description="Guided setup for shared hosting and Docker deployments."
        />

        <Card>
          <div className="mb-4 flex items-center justify-between gap-4">
            <p className="text-sm font-medium text-slate-700">
              Step {stepIndex + 1} of {steps.length}: {steps[stepIndex]}
            </p>
            <Badge tone="muted">{progress}%</Badge>
          </div>
          <div className="h-2 overflow-hidden rounded-full bg-slate-200">
            <div className="h-full bg-slate-900 transition-all" style={{ width: `${progress}%` }} />
          </div>
        </Card>

        {error && <ErrorAlert message={error} />}

        {stepIndex === 0 && (
          <Card>
            {requirementsQuery.isLoading && <LoadingState message="Checking server requirements…" />}
            {requirementsQuery.data && (
              <ul className="space-y-3">
                {requirementsQuery.data.checks.map((check) => (
                  <li key={check.id} className="rounded-lg border border-slate-200 p-3">
                    <div className="flex items-center justify-between gap-2">
                      <span className="font-medium text-slate-900">{check.label}</span>
                      <Badge tone={check.status === 'failed' ? 'warning' : check.status === 'warning' ? 'warning' : 'success'}>
                        {check.status}
                      </Badge>
                    </div>
                    <p className="mt-1 text-sm text-slate-600">{check.message}</p>
                  </li>
                ))}
              </ul>
            )}
            <div className="mt-6 flex justify-end">
              <Button
                disabled={!requirementsQuery.data?.passed}
                onClick={() => setStepIndex(1)}
              >
                Continue
              </Button>
            </div>
          </Card>
        )}

        {stepIndex === 1 && (
          <Card className="space-y-4">
            <label className="block text-sm font-medium text-slate-700">
              Database driver
              <select
                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"
                value={driver}
                onChange={(event) => setDriver(event.target.value)}
              >
                <option value="sqlite">SQLite (simple / local)</option>
                <option value="mysql">MySQL / MariaDB (shared hosting)</option>
                <option value="pgsql">PostgreSQL (Docker prod)</option>
              </select>
            </label>
            {driver !== 'sqlite' && (
              <>
                <Input label="Host" value={host} onChange={(event) => setHost(event.target.value)} />
                <Input label="Port" value={port} onChange={(event) => setPort(event.target.value)} />
                <Input label="Database name" value={database} onChange={(event) => setDatabase(event.target.value)} />
                <Input label="Username" value={username} onChange={(event) => setUsername(event.target.value)} />
                <Input label="Password" type="password" value={password} onChange={(event) => setPassword(event.target.value)} />
              </>
            )}
            {driver === 'sqlite' && (
              <Input
                label="Database file path (optional)"
                value={database}
                onChange={(event) => setDatabase(event.target.value)}
                placeholder="Leave empty for default database/database.sqlite"
              />
            )}
            <div className="flex justify-between gap-2">
              <Button variant="secondary" onClick={() => setStepIndex(0)}>Back</Button>
              <Button onClick={() => void handleDatabaseNext()} disabled={testMutation.isPending || saveMutation.isPending}>
                Save & continue
              </Button>
            </div>
          </Card>
        )}

        {stepIndex === 2 && (
          <Card className="space-y-4">
            <Input label="Site title" value={siteTitle} onChange={(event) => setSiteTitle(event.target.value)} />
            <Input label="Admin email" type="email" value={adminEmail} onChange={(event) => setAdminEmail(event.target.value)} />
            <Input label="Admin password" type="password" value={adminPassword} onChange={(event) => setAdminPassword(event.target.value)} />
            <label className="flex items-center gap-2 text-sm text-slate-700">
              <input
                type="checkbox"
                checked={withStarterSite}
                onChange={(event) => setWithStarterSite(event.target.checked)}
              />
              Install starter site (home page, menus, contact form)
            </label>
            <div className="flex justify-between gap-2">
              <Button variant="secondary" onClick={() => setStepIndex(1)}>Back</Button>
              <Button onClick={() => setStepIndex(3)}>Continue</Button>
            </div>
          </Card>
        )}

        {stepIndex === 3 && (
          <Card className="space-y-4">
            <p className="text-sm text-slate-600">
              Ready to install Luma CMS with your database and admin account.
            </p>
            <div className="flex justify-between gap-2">
              <Button variant="secondary" onClick={() => setStepIndex(2)}>Back</Button>
              <Button onClick={() => void handleFinish()} disabled={finishMutation.isPending}>
                Install now
              </Button>
            </div>
          </Card>
        )}

        <Card>
          <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Setup log</p>
          <div className="max-h-48 space-y-2 overflow-y-auto text-sm">
            {(logsQuery.data?.logs ?? []).length === 0 && (
              <p className="text-slate-500">Log entries appear as setup steps run.</p>
            )}
            {(logsQuery.data?.logs ?? []).map((entry) => (
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
      </div>
    </div>
  )
}
