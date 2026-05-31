import type { ReactNode } from 'react'
import { ThemeToggle } from '../theme/ThemeToggle'

type InstallerThemeLabels = {
  lightLabel: string
  darkLabel: string
  lightAria: string
  darkAria: string
}

type InstallerShellProps = {
  children: ReactNode
  centered?: boolean
  themeLabels?: InstallerThemeLabels
}

export function InstallerShell({ children, centered = false, themeLabels }: InstallerShellProps) {
  return (
    <div className="min-h-screen bg-[var(--luma-color-bg)] px-4 py-10 text-[var(--luma-color-text)]">
      <div className="mx-auto max-w-3xl">
        <div className="flex justify-end pb-4">
          <ThemeToggle labels={themeLabels} />
        </div>
        <div className={centered ? 'flex justify-center' : 'space-y-6'}>{children}</div>
      </div>
    </div>
  )
}
