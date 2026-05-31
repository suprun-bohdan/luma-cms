import { Input } from '../../../shared/components/Input'
import { Select } from '../../../shared/components/Select'
import { Textarea } from '../../../shared/components/Textarea'
import type { Field } from '../../fields/schemas/field'

type DynamicFieldInputProps = {
  field: Field
  value: unknown
  onChange: (value: unknown) => void
}

export function DynamicFieldInput({ field, value, onChange }: DynamicFieldInputProps) {
  switch (field.type) {
    case 'text':
      return (
        <Input
          label={field.name}
          value={typeof value === 'string' ? value : ''}
          onChange={(event) => onChange(event.target.value)}
          required={field.required}
        />
      )
    case 'textarea':
      return (
        <Textarea
          label={field.name}
          value={typeof value === 'string' ? value : ''}
          onChange={(event) => onChange(event.target.value)}
          required={field.required}
        />
      )
    case 'number':
      return (
        <Input
          label={field.name}
          type="number"
          value={typeof value === 'number' || typeof value === 'string' ? String(value) : ''}
          onChange={(event) =>
            onChange(event.target.value === '' ? null : Number(event.target.value))
          }
          required={field.required}
        />
      )
    case 'boolean':
      return (
        <label className="flex items-center gap-2 text-sm text-slate-700">
          <input
            type="checkbox"
            checked={Boolean(value)}
            onChange={(event) => onChange(event.target.checked)}
          />
          {field.name}
          {field.required && <span className="text-red-500">*</span>}
        </label>
      )
    case 'datetime':
      return (
        <Input
          label={field.name}
          type="datetime-local"
          value={typeof value === 'string' ? value.slice(0, 16) : ''}
          onChange={(event) => onChange(event.target.value)}
          required={field.required}
        />
      )
    case 'json':
      return (
        <Textarea
          label={`${field.name} (JSON)`}
          value={
            typeof value === 'string'
              ? value
              : value !== undefined
                ? JSON.stringify(value, null, 2)
                : ''
          }
          onChange={(event) => {
            try {
              onChange(JSON.parse(event.target.value) as unknown)
            } catch {
              onChange(event.target.value)
            }
          }}
          required={field.required}
        />
      )
    default:
      return (
        <Select label={field.name} value="" onChange={() => undefined}>
          <option value="">Unsupported type</option>
        </Select>
      )
  }
}
