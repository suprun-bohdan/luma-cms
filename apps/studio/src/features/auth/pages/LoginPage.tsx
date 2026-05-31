import { useState } from 'react'
import { Navigate } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { Input } from '../../../shared/components/Input'
import { ApiError } from '../../../shared/api/client'
import { formatFieldErrors } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import { useLoginMutation } from '../hooks/useLoginMutation'

export function LoginPage() {
  const { isAuthenticated } = useAuth()
  const loginMutation = useLoginMutation()
  const [email, setEmail] = useState('admin@luma.test')
  const [password, setPassword] = useState('password')

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
    <div className="flex min-h-screen items-center justify-center px-4 py-10">
      <Card className="w-full max-w-md">
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Luma CMS</p>
        <h1 className="mt-2 text-2xl font-semibold text-slate-900">Sign in to Studio</h1>
        <p className="mt-2 text-sm text-slate-600">
          Use your Luma CMS account. Local default: <code>admin@luma.test</code>
        </p>

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

        <p className="mt-4 text-center text-xs text-slate-500">
          API must be running on port 8080 for the Vite proxy.
        </p>
      </Card>
    </div>
  )
}
