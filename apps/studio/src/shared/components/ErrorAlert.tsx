type ErrorAlertProps = {
  message: string
}

export function ErrorAlert({ message }: ErrorAlertProps) {
  return (
    <div className="rounded-lg border border-[var(--luma-color-danger-border)] bg-[var(--luma-color-danger-surface)] px-4 py-3 text-sm text-[var(--luma-color-danger)]">
      {message}
    </div>
  )
}
