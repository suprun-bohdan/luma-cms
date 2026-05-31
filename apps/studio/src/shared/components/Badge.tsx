import type { ReactNode } from 'react'

type BadgeProps = {
  children: ReactNode
  tone?: 'default' | 'success' | 'warning' | 'muted'
}

export function Badge({ children, tone = 'default' }: BadgeProps) {
  const tones = {
    default: 'bg-[var(--luma-color-muted-surface)] text-[var(--luma-color-text)]',
    success: 'bg-[var(--luma-color-success-surface)] text-[var(--luma-color-success-text)]',
    warning: 'bg-[var(--luma-color-warning-surface)] text-[var(--luma-color-warning-text)]',
    muted: 'bg-[var(--luma-color-muted-surface)] text-[var(--luma-color-muted-text)]',
  }

  return (
    <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${tones[tone]}`}>
      {children}
    </span>
  )
}
