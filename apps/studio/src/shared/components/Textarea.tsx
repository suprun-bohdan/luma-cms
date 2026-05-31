import type { TextareaHTMLAttributes } from 'react'
import { fieldErrorClassName, inputClassName, labelClassName } from './formStyles'

type TextareaProps = TextareaHTMLAttributes<HTMLTextAreaElement> & {
  label: string
  error?: string
}

export function Textarea({ label, error, className = '', ...props }: TextareaProps) {
  return (
    <label className="block space-y-1.5">
      <span className={labelClassName()}>{label}</span>
      <textarea
        className={inputClassName(error, className)}
        rows={4}
        {...props}
      />
      {error && <span className={fieldErrorClassName()}>{error}</span>}
    </label>
  )
}
