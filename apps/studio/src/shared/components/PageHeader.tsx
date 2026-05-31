import type { ReactNode } from 'react'

type PageHeaderProps = {
  title: string
  description?: string
  actions?: ReactNode
  breadcrumbs?: ReactNode
}

export function PageHeader({ title, description, actions, breadcrumbs }: PageHeaderProps) {
  return (
    <div className="mb-6 flex flex-col gap-4 border-b border-[var(--luma-color-border)] pb-6 lg:flex-row lg:items-start lg:justify-between">
      <div>
        {breadcrumbs}
        <h1 className="text-2xl font-semibold text-[var(--luma-color-text)]">{title}</h1>
        {description && (
          <p className="mt-2 text-sm text-[var(--luma-color-text-muted)]">{description}</p>
        )}
      </div>
      {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
    </div>
  )
}
