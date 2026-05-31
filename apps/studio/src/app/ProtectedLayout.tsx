import { AppShell } from '../shared/components/AppShell'
import { ProtectedRoute } from '../shared/auth/ProtectedRoute'
import { AuthBootstrap } from './AuthBootstrap'

export function ProtectedLayout() {
  return (
    <AuthBootstrap>
      <AppShell>
        <ProtectedRoute />
      </AppShell>
    </AuthBootstrap>
  )
}
