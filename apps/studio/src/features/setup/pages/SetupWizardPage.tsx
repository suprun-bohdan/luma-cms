import { useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { fetchSystemVersion } from '../../updates/api/systemApi'
import {
  useSetupDatabaseActions,
  useSetupLogs,
  useSetupRequirements,
  useSetupStatus,
} from '../hooks/useSetup'

const steps = [
  'Welcome',
  'Requirements',
  'Database',
  'Site settings',
  'Owner account',
  'Install',
] as const

const blockedPasswords = new Set(['password', 'admin', 'change-me', 'password123'])

function isStrongPassword(password: string): string | null {
  if (password.length < 12) {
    return 'Use at least 12 characters for the owner password.'
  }

  if (blockedPasswords.has(password.toLowerCase())) {
    return 'Choose a stronger password — avoid common defaults like "password" or "admin".'
  }

  return null
}

export function SetupWizardPage() {
  const navigate = useNavigate()
  const statusQuery = useSetupStatus()
  const versionQuery = useQuery({
    queryKey: ['system', 'version'],
    queryFn: fetchSystemVersion,
  })
  const [stepIndex, setStepIndex] = useState(0)
  const requirementsQuery = useSetupRequirements(stepIndex === 1)
  const logsQuery = useSetupLogs(true)
  const { testMutation, saveMutation, finishMutation } = useSetupDatabaseActions()

  const [driver, setDriver] = useState('mysql')
  const [host, setHost] = useState('127.0.0.1')
  const [port, setPort] = useState('3306')
  const [database, setDatabase] = useState('')
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [siteTitle, setSiteTitle] = useState('')
  const [ownerName, setOwnerName] = useState('')
  const [adminEmail, setAdminEmail] = useState('')
  const [adminPassword, setAdminPassword] = useState('')
  const [adminPasswordConfirm, setAdminPasswordConfirm] = useState('')
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
      setStepIndex(3)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Database setup failed')
    }
  }

  function handleOwnerNext() {
    setError(null)

    if (adminPassword !== adminPasswordConfirm) {
      setError('Passwords do not match.')
      return
    }

    const passwordError = isStrongPassword(adminPassword)
    if (passwordError) {
      setError(passwordError)
      return
    }

    setStepIndex(5)
  }

  async function handleFinish() {
    setError(null)
    try {
      const result = await finishMutation.mutateAsync({
        site_title: siteTitle || ownerName || 'My Luma Site',
        admin_email: adminEmail,
        admin_password: adminPassword,
        with_starter_site: withStarterSite,
      })
      navigate(result.redirect.replace(/^\/(?:studio|admin)/, '') || '/login', { replace: true })
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
          description="Browser setup wizard — no terminal required. Complete these steps once, then sign in at /admin/login."
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
          <Card className="space-y-4">
            <p className="text-lg font-semibold text-slate-900">Welcome to Luma CMS</p>
            <HelpText>
              This wizard configures your database, site settings, and owner account. You only run it
              once after uploading the release archive to your hosting account.
            </HelpText>
            <div className="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
              <p>
                <span className="font-medium">Version:</span>{' '}
                {versionQuery.data?.version ?? 'Loading…'}
              </p>
              <p className="mt-1">
                <span className="font-medium">Web root:</span> point your domain at{' '}
                <code>apps/api/public</code>
              </p>
            </div>
            <HelpText>
              Safe defaults are used for cache, session, and queue (file/file/database). Redis is not
              required on shared hosting.
            </HelpText>
            <div className="flex justify-end">
              <Button onClick={() => setStepIndex(1)}>Start setup</Button>
            </div>
          </Card>
        )}

        {stepIndex === 1 && (
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
            <div className="mt-6 flex justify-between gap-2">
              <Button variant="secondary" onClick={() => setStepIndex(0)}>Back</Button>
              <Button
                disabled={!requirementsQuery.data?.passed}
                onClick={() => setStepIndex(2)}
              >
                Continue
              </Button>
            </div>
          </Card>
        )}

        {stepIndex === 2 && (
          <Card className="space-y-4">
            <HelpText>
              Test the connection before continuing. Credentials are saved to <code>.env</code> on
              your server — never displayed in the browser after save.
            </HelpText>
            <label className="block text-sm font-medium text-slate-700">
              Database driver
              <select
                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"
                value={driver}
                onChange={(event) => setDriver(event.target.value)}
              >
                <option value="mysql">MySQL / MariaDB (shared hosting)</option>
                <option value="pgsql">PostgreSQL</option>
                <option value="sqlite">SQLite (simple / local)</option>
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
              <Button variant="secondary" onClick={() => setStepIndex(1)}>Back</Button>
              <Button onClick={() => void handleDatabaseNext()} disabled={testMutation.isPending || saveMutation.isPending}>
                {testMutation.isPending ? 'Testing…' : 'Test connection & continue'}
              </Button>
            </div>
          </Card>
        )}

        {stepIndex === 3 && (
          <Card className="space-y-4">
            <Input
              label="Site title"
              value={siteTitle}
              onChange={(event) => setSiteTitle(event.target.value)}
              placeholder="Bohdan Portfolio"
            />
            <HelpText>
              Used as the site name in settings and as a fallback label for your owner profile.
            </HelpText>
            <div className="flex justify-between gap-2">
              <Button variant="secondary" onClick={() => setStepIndex(2)}>Back</Button>
              <Button onClick={() => setStepIndex(4)}>Continue</Button>
            </div>
          </Card>
        )}

        {stepIndex === 4 && (
          <Card className="space-y-4">
            <Input
              label="Owner name"
              value={ownerName}
              onChange={(event) => setOwnerName(event.target.value)}
              placeholder="Your name"
            />
            <Input label="Owner email" type="email" value={adminEmail} onChange={(event) => setAdminEmail(event.target.value)} />
            <Input label="Owner password" type="password" value={adminPassword} onChange={(event) => setAdminPassword(event.target.value)} />
            <Input
              label="Confirm password"
              type="password"
              value={adminPasswordConfirm}
              onChange={(event) => setAdminPasswordConfirm(event.target.value)}
            />
            <HelpText>
              Minimum 12 characters. The first account receives the owner role with full system access.
            </HelpText>
            <label className="flex items-center gap-2 text-sm text-slate-700">
              <input
                type="checkbox"
                checked={withStarterSite}
                onChange={(event) => setWithStarterSite(event.target.checked)}
              />
              Install starter business site (home page, menus, contact form)
            </label>
            <div className="flex justify-between gap-2">
              <Button variant="secondary" onClick={() => setStepIndex(3)}>Back</Button>
              <Button onClick={handleOwnerNext}>Continue</Button>
            </div>
          </Card>
        )}

        {stepIndex === 5 && (
          <Card className="space-y-4">
            <HelpText>
              Luma CMS will write your configuration, run migrations, create the owner account, and
              optionally install starter content. Session, cache, and queue use file/file/database
              drivers — no Redis required.
            </HelpText>
            <ul className="list-disc space-y-1 pl-5 text-sm text-slate-700">
              <li>Generate application key if missing</li>
              <li>Run database migrations and seed roles</li>
              <li>Create owner: {adminEmail || '(email not set)'}</li>
              <li>Site title: {siteTitle || ownerName || 'My Luma Site'}</li>
              <li>Starter site: {withStarterSite ? 'Yes' : 'No'}</li>
            </ul>
            <div className="flex justify-between gap-2">
              <Button variant="secondary" onClick={() => setStepIndex(4)}>Back</Button>
              <Button onClick={() => void handleFinish()} disabled={finishMutation.isPending}>
                {finishMutation.isPending ? 'Installing…' : 'Install Luma CMS'}
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
