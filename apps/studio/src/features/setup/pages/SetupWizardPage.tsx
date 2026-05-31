import { useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { Checkbox } from '../../../shared/components/Checkbox'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Select } from '../../../shared/components/Select'
import { listItemPanelClassName, mutedPanelClassName } from '../../../shared/components/formStyles'
import { InstallerShell } from '../../../shared/layout/InstallerShell'
import { ApiError } from '../../../shared/api/client'
import {
  useSetupDatabaseActions,
  useSetupLogs,
  useSetupRequirements,
  useSetupStatus,
} from '../hooks/useSetup'
import {
  clearSetupWizardState,
  loadSetupWizardState,
  saveSetupWizardState,
} from '../setupWizardStorage'

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

function requirementsErrorMessage(error: unknown): string {
  if (error instanceof ApiError) {
    if (error.status === 403) {
      return 'Could not read server requirements (access denied). Check nginx /api/ routing.'
    }

    return error.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return 'Could not load server requirements.'
}

export function SetupWizardPage() {
  const navigate = useNavigate()
  const statusQuery = useSetupStatus()
  const savedState = loadSetupWizardState()
  const [stepIndex, setStepIndex] = useState(savedState?.stepIndex ?? 0)
  const requirementsQuery = useSetupRequirements(stepIndex === 1)
  const logsQuery = useSetupLogs(stepIndex >= 5)
  const { testMutation, saveMutation, finishMutation } = useSetupDatabaseActions()

  const [driver, setDriver] = useState(savedState?.driver ?? 'mysql')
  const [host, setHost] = useState(savedState?.host ?? '127.0.0.1')
  const [port, setPort] = useState(savedState?.port ?? '3306')
  const [database, setDatabase] = useState(savedState?.database ?? '')
  const [username, setUsername] = useState(savedState?.username ?? '')
  const [password, setPassword] = useState('')
  const [siteTitle, setSiteTitle] = useState(savedState?.siteTitle ?? '')
  const [ownerName, setOwnerName] = useState(savedState?.ownerName ?? '')
  const [adminEmail, setAdminEmail] = useState(savedState?.adminEmail ?? '')
  const [adminPassword, setAdminPassword] = useState('')
  const [adminPasswordConfirm, setAdminPasswordConfirm] = useState('')
  const [withStarterSite, setWithStarterSite] = useState(savedState?.withStarterSite ?? true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (statusQuery.data?.installed) {
      clearSetupWizardState()
      navigate('/login', { replace: true })
    }
  }, [navigate, statusQuery.data?.installed])

  useEffect(() => {
    if (statusQuery.data?.installed) {
      return
    }

    saveSetupWizardState({
      stepIndex,
      driver,
      host,
      port,
      database,
      username,
      siteTitle,
      ownerName,
      adminEmail,
      withStarterSite,
    })
  }, [
    stepIndex,
    driver,
    host,
    port,
    database,
    username,
    siteTitle,
    ownerName,
    adminEmail,
    withStarterSite,
    statusQuery.data?.installed,
  ])

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
      setError(
        err instanceof ApiError
          ? err.message
          : err instanceof Error
            ? err.message
            : 'Database setup failed',
      )
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
      clearSetupWizardState()
      navigate(result.redirect.replace(/^\/(?:studio|admin)/, '') || '/login', { replace: true })
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Installation failed')
    }
  }

  if (statusQuery.isLoading) {
    return (
      <InstallerShell>
        <PageHeader
          title="Install Luma CMS"
          description="Browser setup wizard — no terminal required. Complete these steps once, then sign in at /admin/login."
        />
        <Card>
          <LoadingState message="Checking installation status…" />
        </Card>
      </InstallerShell>
    )
  }

  return (
    <InstallerShell>
      <PageHeader
        title="Install Luma CMS"
        description="Browser setup wizard — no terminal required. Complete these steps once, then sign in at /admin/login."
      />

      <Card>
        <div className="mb-4 flex items-center justify-between gap-4">
          <p className="text-sm font-medium text-[var(--luma-color-text)]">
            Step {stepIndex + 1} of {steps.length}: {steps[stepIndex]}
          </p>
          <Badge tone="muted">{progress}%</Badge>
        </div>
        <div className="h-2 overflow-hidden rounded-full bg-[var(--luma-color-progress-track)]">
          <div
            className="h-full bg-[var(--luma-color-progress-fill)] transition-all"
            style={{ width: `${progress}%` }}
          />
        </div>
      </Card>

      {error && <ErrorAlert message={error} />}

      {stepIndex === 0 && (
        <Card className="space-y-4">
          <p className="text-lg font-semibold text-[var(--luma-color-text)]">Welcome to Luma CMS</p>
          <HelpText>
            This wizard configures your database, site settings, and owner account. You only run it
            once after uploading the release archive to your hosting account.
          </HelpText>
          <div className={mutedPanelClassName()}>
            <p>
              <span className="font-medium">Version:</span>{' '}
              {statusQuery.data?.version ?? 'Unknown'}
            </p>
            <p className="mt-1">
              <span className="font-medium">Web root:</span> point your domain at the folder
              containing <code>index.php</code> and <code>apps/</code>
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
          {requirementsQuery.isError && (
            <div className="space-y-3">
              <ErrorAlert message={requirementsErrorMessage(requirementsQuery.error)} />
              <Button variant="secondary" onClick={() => void requirementsQuery.refetch()}>
                Retry requirements check
              </Button>
            </div>
          )}
          {requirementsQuery.data && (
            <ul className="space-y-3">
              {requirementsQuery.data.checks.map((check) => (
                <li key={check.id} className={listItemPanelClassName()}>
                  <div className="flex items-center justify-between gap-2">
                    <span className="font-medium text-[var(--luma-color-text)]">{check.label}</span>
                    <Badge tone={check.status === 'failed' ? 'warning' : check.status === 'warning' ? 'warning' : 'success'}>
                      {check.status}
                    </Badge>
                  </div>
                  <p className="mt-1 text-sm text-[var(--luma-color-text-muted)]">{check.message}</p>
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
          <Select
            label="Database driver"
            value={driver}
            onChange={(event) => setDriver(event.target.value)}
          >
            <option value="mysql">MySQL (shared hosting)</option>
            <option value="mariadb">MariaDB</option>
            <option value="pgsql">PostgreSQL</option>
            <option value="sqlite">SQLite (simple / local)</option>
          </Select>
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
              {testMutation.isPending
                ? 'Testing…'
                : saveMutation.isPending
                  ? 'Saving…'
                  : 'Test connection & continue'}
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
          <Checkbox
            label="Install starter business site (home page, menus, contact form)"
            checked={withStarterSite}
            onChange={(event) => setWithStarterSite(event.target.checked)}
          />
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
          <ul className="list-disc space-y-1 pl-5 text-sm text-[var(--luma-color-text)]">
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
        <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-[var(--luma-color-muted-text)]">
          Setup log
        </p>
        <div className="max-h-48 space-y-2 overflow-y-auto text-sm">
          {(logsQuery.data?.logs ?? []).length === 0 && (
            <p className="text-[var(--luma-color-text-muted)]">Log entries appear as setup steps run.</p>
          )}
          {(logsQuery.data?.logs ?? []).map((entry) => (
            <div key={entry.id} className={listItemPanelClassName()}>
              <div className="flex items-center justify-between gap-2">
                <span className="font-medium text-[var(--luma-color-text)]">{entry.step}</span>
                <Badge tone="muted">{entry.status}</Badge>
              </div>
              <p className="text-[var(--luma-color-text-muted)]">{entry.message}</p>
            </div>
          ))}
        </div>
      </Card>
    </InstallerShell>
  )
}
