type SuccessAlertProps = {
  message: string
}

export function SuccessAlert({ message }: SuccessAlertProps) {
  return (
    <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
      {message}
    </div>
  )
}
