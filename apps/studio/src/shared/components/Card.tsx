import type { ReactNode } from 'react'

type CardProps = {
  children: ReactNode
  className?: string
}

export function Card({ children, className = '' }: CardProps) {
  return (
    <div
      className={`rounded-xl border border-[var(--luma-color-border)] bg-[var(--luma-color-surface)] p-6 shadow-[var(--luma-shadow-sm)] ${className}`}
    >
      {children}
    </div>
  )
}
