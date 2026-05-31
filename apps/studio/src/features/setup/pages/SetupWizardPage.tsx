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
import { SetupI18nProvider } from '../i18n/SetupI18nContext'
import { translateApiError } from '../i18n/translateApiError'
import { translateRequirementCheck } from '../i18n/setupI18nHelpers'
import { useSetupI18n } from '../i18n/useSetupI18n'
import {
  clearSetupWizardState,
  loadSetupWizardState,
  saveSetupWizardState,
} from '../setupWizardStorage'

const blockedPasswords = new Set(['password', 'admin', 'change-me', 'password123'])

const stepKeys = [
  'steps.welcome',
  'steps.requirements',
  'steps.database',
  'steps.site',
  'steps.owner',
  'steps.install',
] as const

function SetupWizardContent() {
  const navigate = useNavigate()
  const { t, locale, setLocale, suggestedLocale, regionHint, languages } = useSetupI18n()
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
  const [allowWeakPassword, setAllowWeakPassword] = useState(savedState?.allowWeakPassword ?? false)
  const [error, setError] = useState<string | null>(null)

  const steps = useMemo(() => stepKeys.map((key) => t(key)), [t])

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
      locale,
      allowWeakPassword,
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
    locale,
    allowWeakPassword,
    statusQuery.data?.installed,
  ])

  const progress = useMemo(() => Math.round(((stepIndex + 1) / steps.length) * 100), [stepIndex, steps.length])

  const databasePayload = {
    driver,
    host,
    port: Number(port),
    database: driver === 'sqlite' ? database || undefined : database,
    username,
    password,
  }

  function validatePassword(): string | null {
    if (adminPassword !== adminPasswordConfirm) {
      return t('owner.passwordMismatch')
    }

    if (allowWeakPassword) {
      if (adminPassword.length < 8) {
        return t('errors.password.minLength', { min: 8 })
      }

      return null
    }

    if (adminPassword.length < 12) {
      return t('errors.password.minLength', { min: 12 })
    }

    if (blockedPasswords.has(adminPassword.toLowerCase())) {
      return t('errors.password.strongRequired')
    }

    return null
  }

  function resolveApiError(err: unknown, fallbackKey: string): string {
    if (err instanceof ApiError) {
      if (err.status === 429) {
        return t('errors.finish.rateLimited')
      }

      if (err.errors?.admin_password?.[0]?.startsWith('errors.')) {
        const key = err.errors.admin_password[0]
        return t(key, key === 'errors.password.minLength' ? { min: 12 } : undefined)
      }

      return translateApiError(t, err, fallbackKey)
    }

    if (err instanceof Error && err.message) {
      return err.message
    }

    return t(fallbackKey)
  }

  function requirementsErrorMessage(errorValue: unknown): string {
    if (errorValue instanceof ApiError) {
      if (errorValue.status === 403) {
        return t('errors.requirements.accessDenied')
      }

      return translateApiError(t, errorValue, 'errors.requirements.loadFailed')
    }

    if (errorValue instanceof Error) {
      return errorValue.message
    }

    return t('errors.requirements.loadFailed')
  }

  async function handleDatabaseNext() {
    setError(null)
    try {
      await testMutation.mutateAsync(databasePayload)
      await saveMutation.mutateAsync(databasePayload)
      setStepIndex(3)
    } catch (err) {
      setError(resolveApiError(err, 'errors.database.saveFailed'))
    }
  }

  function handleOwnerNext() {
    setError(null)

    const passwordError = validatePassword()
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
        site_title: siteTitle || ownerName || t('install.defaultSiteTitle'),
        admin_email: adminEmail,
        admin_password: adminPassword,
        with_starter_site: withStarterSite,
        allow_weak_password: allowWeakPassword,
      })
      clearSetupWizardState()
      navigate(result.redirect.replace(/^\/(?:studio|admin)/, '') || '/login', { replace: true })
    } catch (err) {
      setError(resolveApiError(err, 'errors.finish.installFailed'))
    }
  }

  const themeLabels = {
    lightLabel: t('theme.dark'),
    darkLabel: t('theme.light'),
    lightAria: t('theme.switchToDark'),
    darkAria: t('theme.switchToLight'),
  }

  if (statusQuery.isLoading) {
    return (
      <InstallerShell themeLabels={themeLabels}>
        <PageHeader title={t('meta.title')} description={t('meta.description')} />
        <Card>
          <LoadingState message={t('status.checking')} />
        </Card>
      </InstallerShell>
    )
  }

  return (
    <InstallerShell themeLabels={themeLabels}>
      <PageHeader title={t('meta.title')} description={t('meta.description')} />

      <Card>
        <div className="mb-4 flex items-center justify-between gap-4">
          <p className="text-sm font-medium text-[var(--luma-color-text)]">
            {t('common.stepProgress', {
              current: stepIndex + 1,
              total: steps.length,
              step: steps[stepIndex],
            })}
          </p>
          <Badge tone="muted">{t('common.percentComplete', { percent: progress })}</Badge>
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
          <p className="text-lg font-semibold text-[var(--luma-color-text)]">{t('welcome.title')}</p>
          <HelpText>{t('welcome.intro')}</HelpText>

          <div className="space-y-2">
            <Select
              label={t('language.label')}
              value={locale}
              onChange={(event) => setLocale(event.target.value)}
            >
              {languages.map((language) => (
                <option key={language.code} value={language.code}>
                  {language.nativeName}
                  {language.code === suggestedLocale ? ` — ${t('language.suggested')}` : ''}
                </option>
              ))}
            </Select>
            {regionHint && (
              <HelpText>{t('language.regionHint', { region: regionHint })}</HelpText>
            )}
          </div>

          <div className={mutedPanelClassName()}>
            <p>
              <span className="font-medium">{t('welcome.version')}</span>{' '}
              {statusQuery.data?.version ?? t('common.unknown')}
            </p>
            <p className="mt-1">
              <span className="font-medium">{t('welcome.webRoot')}</span> {t('welcome.webRootHelp')}
            </p>
          </div>
          <HelpText>{t('welcome.defaultsHelp')}</HelpText>
          <div className="flex justify-end">
            <Button onClick={() => setStepIndex(1)}>{t('welcome.start')}</Button>
          </div>
        </Card>
      )}

      {stepIndex === 1 && (
        <Card>
          {requirementsQuery.isLoading && <LoadingState message={t('requirements.loading')} />}
          {requirementsQuery.isError && (
            <div className="space-y-3">
              <ErrorAlert message={requirementsErrorMessage(requirementsQuery.error)} />
              <Button variant="secondary" onClick={() => void requirementsQuery.refetch()}>
                {t('requirements.retry')}
              </Button>
            </div>
          )}
          {requirementsQuery.data && (
            <ul className="space-y-3">
              {requirementsQuery.data.checks.map((check) => {
                const translated = translateRequirementCheck(check, t)

                return (
                  <li key={check.id} className={listItemPanelClassName()}>
                    <div className="flex items-center justify-between gap-2">
                      <span className="font-medium text-[var(--luma-color-text)]">{translated.label}</span>
                      <Badge
                        tone={
                          check.status === 'failed'
                            ? 'warning'
                            : check.status === 'warning'
                              ? 'warning'
                              : 'success'
                        }
                      >
                        {t(`status.${check.status}`)}
                      </Badge>
                    </div>
                    <p className="mt-1 text-sm text-[var(--luma-color-text-muted)]">{translated.message}</p>
                  </li>
                )
              })}
            </ul>
          )}
          <div className="mt-6 flex justify-between gap-2">
            <Button variant="secondary" onClick={() => setStepIndex(0)}>
              {t('common.back')}
            </Button>
            <Button disabled={!requirementsQuery.data?.passed} onClick={() => setStepIndex(2)}>
              {t('common.continue')}
            </Button>
          </div>
        </Card>
      )}

      {stepIndex === 2 && (
        <Card className="space-y-4">
          <HelpText>{t('database.help')}</HelpText>
          <Select
            label={t('database.driver.label')}
            value={driver}
            onChange={(event) => setDriver(event.target.value)}
          >
            <option value="mysql">{t('database.driver.mysql')}</option>
            <option value="mariadb">{t('database.driver.mariadb')}</option>
            <option value="pgsql">{t('database.driver.pgsql')}</option>
            <option value="sqlite">{t('database.driver.sqlite')}</option>
          </Select>
          {driver !== 'sqlite' && (
            <>
              <Input label={t('database.host')} value={host} onChange={(event) => setHost(event.target.value)} />
              <Input label={t('database.port')} value={port} onChange={(event) => setPort(event.target.value)} />
              <Input
                label={t('database.name')}
                value={database}
                onChange={(event) => setDatabase(event.target.value)}
              />
              <Input
                label={t('database.username')}
                value={username}
                onChange={(event) => setUsername(event.target.value)}
              />
              <Input
                label={t('database.password')}
                type="password"
                value={password}
                onChange={(event) => setPassword(event.target.value)}
              />
            </>
          )}
          {driver === 'sqlite' && (
            <Input
              label={t('database.sqlitePath')}
              value={database}
              onChange={(event) => setDatabase(event.target.value)}
              placeholder={t('database.sqlitePlaceholder')}
            />
          )}
          <div className="flex justify-between gap-2">
            <Button variant="secondary" onClick={() => setStepIndex(1)}>
              {t('common.back')}
            </Button>
            <Button
              onClick={() => void handleDatabaseNext()}
              disabled={testMutation.isPending || saveMutation.isPending}
            >
              {testMutation.isPending
                ? t('database.testing')
                : saveMutation.isPending
                  ? t('database.saving')
                  : t('database.testAndContinue')}
            </Button>
          </div>
        </Card>
      )}

      {stepIndex === 3 && (
        <Card className="space-y-4">
          <Input
            label={t('site.title')}
            value={siteTitle}
            onChange={(event) => setSiteTitle(event.target.value)}
            placeholder={t('site.titlePlaceholder')}
          />
          <HelpText>{t('site.help')}</HelpText>
          <div className="flex justify-between gap-2">
            <Button variant="secondary" onClick={() => setStepIndex(2)}>
              {t('common.back')}
            </Button>
            <Button onClick={() => setStepIndex(4)}>{t('common.continue')}</Button>
          </div>
        </Card>
      )}

      {stepIndex === 4 && (
        <Card className="space-y-4">
          <Input
            label={t('owner.name')}
            value={ownerName}
            onChange={(event) => setOwnerName(event.target.value)}
            placeholder={t('owner.namePlaceholder')}
          />
          <Input
            label={t('owner.email')}
            type="email"
            value={adminEmail}
            onChange={(event) => setAdminEmail(event.target.value)}
          />
          <Input
            label={t('owner.password')}
            type="password"
            value={adminPassword}
            onChange={(event) => setAdminPassword(event.target.value)}
          />
          <Input
            label={t('owner.passwordConfirm')}
            type="password"
            value={adminPasswordConfirm}
            onChange={(event) => setAdminPasswordConfirm(event.target.value)}
          />
          <HelpText>{allowWeakPassword ? t('owner.passwordHelpWeak') : t('owner.passwordHelp')}</HelpText>
          <Checkbox
            label={t('owner.allowWeakPassword')}
            checked={allowWeakPassword}
            onChange={(event) => setAllowWeakPassword(event.target.checked)}
          />
          {allowWeakPassword && <HelpText>{t('owner.allowWeakPasswordHelp')}</HelpText>}
          <Checkbox
            label={t('owner.starterSite')}
            checked={withStarterSite}
            onChange={(event) => setWithStarterSite(event.target.checked)}
          />
          <div className="flex justify-between gap-2">
            <Button variant="secondary" onClick={() => setStepIndex(3)}>
              {t('common.back')}
            </Button>
            <Button onClick={handleOwnerNext}>{t('common.continue')}</Button>
          </div>
        </Card>
      )}

      {stepIndex === 5 && (
        <Card className="space-y-4">
          <HelpText>{t('install.help')}</HelpText>
          <ul className="list-disc space-y-1 pl-5 text-sm text-[var(--luma-color-text)]">
            <li>{t('install.generateKey')}</li>
            <li>{t('install.migrations')}</li>
            <li>
              {t('install.createOwner', {
                email: adminEmail || t('install.emailNotSet'),
              })}
            </li>
            <li>
              {t('install.siteTitle', {
                title: siteTitle || ownerName || t('install.defaultSiteTitle'),
              })}
            </li>
            <li>
              {t('install.starterSite', {
                value: withStarterSite ? t('common.yes') : t('common.no'),
              })}
            </li>
          </ul>
          <div className="flex justify-between gap-2">
            <Button variant="secondary" onClick={() => setStepIndex(4)}>
              {t('common.back')}
            </Button>
            <Button onClick={() => void handleFinish()} disabled={finishMutation.isPending}>
              {finishMutation.isPending ? t('install.installing') : t('install.button')}
            </Button>
          </div>
        </Card>
      )}

      <Card>
        <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-[var(--luma-color-muted-text)]">
          {t('log.title')}
        </p>
        <div className="max-h-48 space-y-2 overflow-y-auto text-sm">
          {(logsQuery.data?.logs ?? []).length === 0 && (
            <p className="text-[var(--luma-color-text-muted)]">{t('log.empty')}</p>
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

export function SetupWizardPage() {
  const savedState = loadSetupWizardState()

  return <SetupI18nProvider initialLocale={savedState?.locale}><SetupWizardContent /></SetupI18nProvider>
}
