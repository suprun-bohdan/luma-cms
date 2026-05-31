import type { ReactNode } from 'react'

type TopBarProps = {
  children?: ReactNode
}

export function TopBar({ children }: TopBarProps) {
  if (!children) {
    return null
  }

  return (
    <header className="flex min-h-[var(--luma-topbar-height)] items-center border-b border-slate-200 bg-white px-4 lg:px-8">
      {children}
    </header>
  )
}
