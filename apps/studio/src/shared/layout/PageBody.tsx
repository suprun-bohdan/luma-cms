import type { ReactNode } from 'react'

type PageBodyProps = {
  children: ReactNode
  className?: string
}

export function PageBody({ children, className = '' }: PageBodyProps) {
  return (
    <div className={`mx-auto w-full max-w-[var(--luma-content-max-width)] ${className}`}>
      {children}
    </div>
  )
}
