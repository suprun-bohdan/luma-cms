type DangerNoticeProps = {
  title?: string
  children: React.ReactNode
}

export function DangerNotice({ title, children }: DangerNoticeProps) {
  return (
    <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
      {title && <p className="font-medium">{title}</p>}
      <div className={title ? 'mt-1' : undefined}>{children}</div>
    </div>
  )
}
