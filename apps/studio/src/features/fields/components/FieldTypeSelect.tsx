import { fieldTypeLabels, type FieldType } from '../schemas/field'
import { Select } from '../../../shared/components/Select'

type FieldTypeSelectProps = {
  value: FieldType
  onChange: (value: FieldType) => void
}

export function FieldTypeSelect({ value, onChange }: FieldTypeSelectProps) {
  return (
    <Select
      label="Type"
      value={value}
      onChange={(event) => onChange(event.target.value as FieldType)}
    >
      {Object.entries(fieldTypeLabels).map(([type, label]) => (
        <option key={type} value={type}>
          {label}
        </option>
      ))}
    </Select>
  )
}
