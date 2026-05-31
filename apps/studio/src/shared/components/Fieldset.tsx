import type { ReactNode } from 'react'

type FieldsetProps = {
  legend: string
  children: ReactNode
  className?: string
}

export function Fieldset({ legend, children, className = '' }: FieldsetProps) {
  return (
    <fieldset
      className={`space-y-4 rounded-lg border border-[var(--luma-color-border)] p-4 ${className}`}
    >
      <legend className="px-1 text-sm font-semibold text-[var(--luma-color-text)]">{legend}</legend>
      {children}
    </fieldset>
  )
}
