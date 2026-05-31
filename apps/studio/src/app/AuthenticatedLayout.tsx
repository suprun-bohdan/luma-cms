import { AuthBootstrap } from './AuthBootstrap'
import { ProtectedRoute } from '../shared/auth/ProtectedRoute'

export function AuthenticatedLayout() {
  return (
    <AuthBootstrap>
      <ProtectedRoute />
    </AuthBootstrap>
  )
}
