import type { ButtonHTMLAttributes, ReactNode } from 'react'

type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost'

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: ButtonVariant
  children: ReactNode
}

const variantClasses: Record<ButtonVariant, string> = {
  primary:
    'bg-[var(--luma-color-primary)] text-[var(--luma-color-primary-contrast)] hover:bg-[var(--luma-color-primary-hover)]',
  secondary:
    'border border-[var(--luma-color-border)] bg-[var(--luma-color-surface)] text-[var(--luma-color-text)] hover:bg-[var(--luma-color-surface-muted)]',
  danger: 'bg-[var(--luma-color-danger)] text-white hover:opacity-90',
  ghost:
    'text-[var(--luma-color-text-muted)] hover:bg-[var(--luma-color-surface-muted)] hover:text-[var(--luma-color-text)]',
}

export function Button({
  variant = 'primary',
  className = '',
  children,
  ...props
}: ButtonProps) {
  return (
    <button
      className={`inline-flex cursor-pointer items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50 ${variantClasses[variant]} ${className}`}
      {...props}
    >
      {children}
    </button>
  )
}
