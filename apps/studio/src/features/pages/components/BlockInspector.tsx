import { Input } from '../../../shared/components/Input'
import { Textarea } from '../../../shared/components/Textarea'
import { SettingsPanel } from '../../../shared/layout/SplitPane'
import { getBlockDefinition } from '../data/blockDefinitions'
import type { PageContent } from '../schemas/page'

type BlockInspectorProps = {
  block: PageContent['blocks'][number] | null
  onChange: (blockId: string, props: Record<string, unknown>) => void
}

export function BlockInspector({ block, onChange }: BlockInspectorProps) {
  if (!block) {
    return (
      <SettingsPanel title="Block settings">
        <p className="text-sm text-slate-500">Select a block to edit its content.</p>
      </SettingsPanel>
    )
  }

  const definition = getBlockDefinition(block.type)
  if (!definition) {
    return (
      <SettingsPanel title="Block settings">
        <p className="text-sm text-slate-500">Unknown block type.</p>
      </SettingsPanel>
    )
  }

  function updateProp(name: string, value: string) {
    if (!block) {
      return
    }

    onChange(block.id, {
      ...block.props,
      [name]: value,
    })
  }

  return (
    <SettingsPanel title={definition.label}>
      <div className="space-y-4">
        {definition.fields.map((field) => {
          const value = typeof block.props[field.name] === 'string'
            ? (block.props[field.name] as string)
            : ''

          if (field.type === 'textarea') {
            return (
              <Textarea
                key={field.name}
                label={field.label}
                id={`block-${block.id}-${field.name}`}
                rows={5}
                value={value}
                placeholder={field.placeholder}
                onChange={(event) => updateProp(field.name, event.target.value)}
              />
            )
          }

          return (
            <Input
              key={field.name}
              label={field.label}
              id={`block-${block.id}-${field.name}`}
              value={value}
              placeholder={field.placeholder}
              className={field.name.includes('uuid') || field.name.includes('slug') ? 'font-mono' : undefined}
              onChange={(event) => updateProp(field.name, event.target.value)}
            />
          )
        })}
      </div>
    </SettingsPanel>
  )
}
