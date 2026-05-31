export function inputClassName(error?: string, className = ''): string {
  return [
    'w-full rounded-lg border px-3 py-2 text-sm outline-none',
    'bg-[var(--luma-color-surface)] text-[var(--luma-color-text)]',
    'focus:border-[var(--luma-color-border)] focus:ring-2 focus:ring-[var(--luma-color-focus-ring)]',
    error ? 'border-[var(--luma-color-danger)]' : 'border-[var(--luma-color-border)]',
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

export function mutedPanelClassName(className = ''): string {
  return [
    'rounded-lg border border-[var(--luma-color-border)] bg-[var(--luma-color-surface-muted)] px-4 py-3 text-sm text-[var(--luma-color-text)]',
    className,
  ]
    .filter(Boolean)
    .join(' ')
}

export function listItemPanelClassName(className = ''): string {
  return [
    'rounded-lg border border-[var(--luma-color-border)] bg-[var(--luma-color-surface)] p-3',
    className,
  ]
    .filter(Boolean)
    .join(' ')
}
