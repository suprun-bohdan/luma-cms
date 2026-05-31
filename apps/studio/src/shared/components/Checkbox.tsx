import type { InputHTMLAttributes } from 'react'

type CheckboxProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> & {
  label: string
  error?: string
}

export function Checkbox({ label, error, id, className = '', ...props }: CheckboxProps) {
  const inputId = id ?? props.name

  return (
    <label className="flex cursor-pointer items-start gap-2">
      <input
        id={inputId}
        type="checkbox"
        className={`mt-1 h-4 w-4 rounded border-[var(--luma-color-border)] text-[var(--luma-color-primary)] focus:ring-2 focus:ring-[var(--luma-color-focus-ring)] ${className}`}
        {...props}
      />
      <span className="space-y-1">
        <span className="text-sm font-medium text-[var(--luma-color-text)]">{label}</span>
        {error && <span className="block text-xs text-[var(--luma-color-danger)]">{error}</span>}
      </span>
    </label>
  )
}
