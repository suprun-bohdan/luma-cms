import type { SelectHTMLAttributes, ReactNode } from 'react'
import { fieldErrorClassName, inputClassName, labelClassName } from './formStyles'

type SelectProps = SelectHTMLAttributes<HTMLSelectElement> & {
  label: string
  error?: string
  children: ReactNode
}

export function Select({ label, error, className = '', children, ...props }: SelectProps) {
  return (
    <label className="block space-y-1.5">
      <span className={labelClassName()}>{label}</span>
      <select className={`${inputClassName(error, className)} cursor-pointer`} {...props}>
        {children}
      </select>
      {error && <span className={fieldErrorClassName()}>{error}</span>}
    </label>
  )
}
