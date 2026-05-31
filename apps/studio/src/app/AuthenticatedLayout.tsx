import { AuthBootstrap } from './AuthBootstrap'
import { InstallGate } from './InstallGate'
import { ProtectedRoute } from '../shared/auth/ProtectedRoute'

export function AuthenticatedLayout() {
  return (
    <AuthBootstrap>
      <InstallGate>
        <ProtectedRoute />
      </InstallGate>
    </AuthBootstrap>
  )
}
