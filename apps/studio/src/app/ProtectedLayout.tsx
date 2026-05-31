import { Outlet } from 'react-router-dom'
import { AppShell } from '../shared/components/AppShell'

export function ProtectedLayout() {
  return (
    <AppShell>
      <Outlet />
    </AppShell>
  )
}
