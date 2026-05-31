import type { ReactNode } from 'react'
import { AppSidebar } from './AppSidebar'
import { PageBody } from './PageBody'
import { TopBar } from './TopBar'

type AdminShellProps = {
  children: ReactNode
  topBar?: ReactNode
}

export function AdminShell({ children, topBar }: AdminShellProps) {
  return (
    <div className="min-h-screen bg-[var(--luma-color-bg)] lg:grid lg:grid-cols-[var(--luma-sidebar-width)_1fr]">
      <AppSidebar />
      <div className="flex min-h-screen flex-col">
        <TopBar>{topBar}</TopBar>
        <main className="flex-1 px-4 py-6 lg:px-8">
          <PageBody>{children}</PageBody>
        </main>
      </div>
    </div>
  )
}
