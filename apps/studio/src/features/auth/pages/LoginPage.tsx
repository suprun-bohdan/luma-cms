import { useState } from 'react'
import { Navigate } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { InstallerShell } from '../../../shared/layout/InstallerShell'
import { ApiError } from '../../../shared/api/client'
import { formatFieldErrors } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import { useSetupStatus } from '../../setup/hooks/useSetup'
import { useLoginMutation } from '../hooks/useLoginMutation'

export function LoginPage() {
  const { isAuthenticated } = useAuth()
  const statusQuery = useSetupStatus()
  const loginMutation = useLoginMutation()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')

  if (statusQuery.isLoading) {
    return (
      <InstallerShell centered>
        <Card className="w-full max-w-md">
          <LoadingState message="Checking installation status…" />
        </Card>
      </InstallerShell>
    )
  }

  if (!statusQuery.data?.installed) {
    return <Navigate to="/setup" replace />
  }

  if (isAuthenticated) {
    return <Navigate to="/collections" replace />
  }

  const errorMessage =
    loginMutation.error instanceof ApiError
      ? formatFieldErrors(loginMutation.error.errors) || loginMutation.error.message
      : loginMutation.error instanceof Error
        ? loginMutation.error.message
        : null

  return (
    <InstallerShell centered>
      <Card className="w-full max-w-md">
        <p className="text-xs font-semibold uppercase tracking-wide text-[var(--luma-color-muted-text)]">
          Luma CMS
        </p>
        <h1 className="mt-2 text-2xl font-semibold text-[var(--luma-color-text)]">
          Sign in to Luma Studio
        </h1>
        <HelpText className="mt-2">
          Use the owner account you created during setup. If you have not installed Luma CMS yet,
          open <code>/admin/setup</code> in your browser.
        </HelpText>

        <form
          className="mt-6 space-y-4"
          onSubmit={(event) => {
            event.preventDefault()
            loginMutation.mutate({ email, password })
          }}
        >
          <Input
            label="Email"
            name="email"
            type="email"
            autoComplete="email"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            required
          />
          <Input
            label="Password"
            name="password"
            type="password"
            autoComplete="current-password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            required
          />

          {errorMessage && <ErrorAlert message={errorMessage} />}

          <Button type="submit" className="w-full" disabled={loginMutation.isPending}>
            {loginMutation.isPending ? 'Signing in…' : 'Sign in'}
          </Button>
        </form>
      </Card>
    </InstallerShell>
  )
}
