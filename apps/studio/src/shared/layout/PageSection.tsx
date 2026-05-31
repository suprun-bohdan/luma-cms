import type { ReactNode } from 'react'
import { Card } from '../components/Card'

type PageSectionProps = {
  title?: string
  children: ReactNode
  className?: string
}

export function PageSection({ title, children, className = '' }: PageSectionProps) {
  return (
    <Card className={className}>
      {title && <h2 className="mb-4 text-sm font-semibold text-slate-900">{title}</h2>}
      {children}
    </Card>
  )
}
