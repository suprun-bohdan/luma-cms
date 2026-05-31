type PermissionNoticeProps = {
  title?: string
  message: string
}

export function PermissionNotice({
  title = 'Permission required',
  message,
}: PermissionNoticeProps) {
  return (
    <div className="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800">
      <p className="font-medium text-slate-900">{title}</p>
      <p className="mt-1 text-slate-700">{message}</p>
    </div>
  )
}
