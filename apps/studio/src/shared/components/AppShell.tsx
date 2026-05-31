import type { ReactNode } from 'react'
import { AdminShell } from '../layout/AdminShell'

type AppShellProps = {
  children: ReactNode
}

/** @deprecated Use AdminShell from shared/layout. Kept for backward compatibility. */
export function AppShell({ children }: AppShellProps) {
  return <AdminShell>{children}</AdminShell>
}

export { SidebarNav } from '../layout/AppSidebar'
