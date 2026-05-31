export function inputClassName(error?: string, className = ''): string {
  return [
    'w-full rounded-lg border px-3 py-2 text-sm outline-none',
    'focus:border-slate-400 focus:ring-2 focus:ring-slate-200',
    error ? 'border-red-400' : 'border-[var(--luma-color-border)]',
    className,
  ]
    .filter(Boolean)
    .join(' ')
}

export function labelClassName(): string {
  return 'text-sm font-medium text-[var(--luma-color-text)]'
}

export function fieldErrorClassName(): string {
  return 'text-xs text-[var(--luma-color-danger)]'
}
