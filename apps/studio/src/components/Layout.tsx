import type { ReactNode } from 'react'

type LayoutProps = {
  children: ReactNode
}

export function Layout({ children }: LayoutProps) {
  return (
    <div className="mx-auto flex min-h-screen max-w-3xl flex-col px-6 py-10">
      <header className="mb-10 border-b border-slate-200 pb-6">
        <p className="text-sm font-medium uppercase tracking-wide text-slate-500">
          Luma CMS
        </p>
        <h1 className="mt-1 text-2xl font-semibold">Studio</h1>
        <p className="mt-2 text-sm text-slate-600">
          Pre-alpha admin interface. Content workflows are not implemented yet.
        </p>
      </header>
      <main className="flex-1">{children}</main>
    </div>
  )
}
