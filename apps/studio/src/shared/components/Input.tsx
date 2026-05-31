import type { InputHTMLAttributes } from 'react'
import { fieldErrorClassName, inputClassName, labelClassName } from './formStyles'

type InputProps = InputHTMLAttributes<HTMLInputElement> & {
  label: string
  error?: string
}

export function Input({ label, error, id, className = '', ...props }: InputProps) {
  const inputId = id ?? props.name

  return (
    <label className="block space-y-1.5">
      <span className={labelClassName()}>{label}</span>
      <input
        id={inputId}
        className={inputClassName(error, className)}
        {...props}
      />
      {error && <span className={fieldErrorClassName()}>{error}</span>}
    </label>
  )
}
