import type { ReactNode } from 'react'

type SplitPaneProps = {
  left: ReactNode
  center?: ReactNode
  right?: ReactNode
  className?: string
}

export function SplitPane({ left, center, right, className = '' }: SplitPaneProps) {
  if (center !== undefined && right !== undefined) {
    return (
      <div
        className={`grid min-h-[24rem] gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)_minmax(18rem,0.75fr)] ${className}`}
      >
        <div className="min-w-0">{left}</div>
        <div className="min-w-0">{center}</div>
        <div className="min-w-0">{right}</div>
      </div>
    )
  }

  return (
    <div className={`grid min-h-[24rem] gap-4 lg:grid-cols-2 ${className}`}>
      <div className="min-w-0">{left}</div>
      <div className="min-w-0">{right ?? center}</div>
    </div>
  )
}

export function PreviewPane({ children, title }: { children: ReactNode; title?: string }) {
  return (
    <section className="flex h-full min-h-[20rem] flex-col overflow-hidden rounded-xl border border-slate-200 bg-white">
      {title && (
        <div className="border-b border-slate-200 px-4 py-3">
          <h2 className="text-sm font-semibold text-slate-900">{title}</h2>
        </div>
      )}
      <div className="flex-1 overflow-auto p-4">{children}</div>
    </section>
  )
}

export function SettingsPanel({ children, title }: { children: ReactNode; title?: string }) {
  return (
    <aside className="flex h-full min-h-[20rem] flex-col overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
      {title && (
        <div className="border-b border-slate-200 bg-white px-4 py-3">
          <h2 className="text-sm font-semibold text-slate-900">{title}</h2>
        </div>
      )}
      <div className="flex-1 overflow-auto p-4">{children}</div>
    </aside>
  )
}
