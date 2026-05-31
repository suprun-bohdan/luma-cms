import type { ReactNode } from 'react'

type BadgeProps = {
  children: ReactNode
  tone?: 'default' | 'success' | 'warning' | 'muted'
}

export function Badge({ children, tone = 'default' }: BadgeProps) {
  const tones = {
    default: 'bg-slate-100 text-slate-700',
    success: 'bg-emerald-100 text-emerald-800',
    warning: 'bg-amber-100 text-amber-800',
    muted: 'bg-slate-50 text-slate-500',
  }

  return (
    <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${tones[tone]}`}>
      {children}
    </span>
  )
}
