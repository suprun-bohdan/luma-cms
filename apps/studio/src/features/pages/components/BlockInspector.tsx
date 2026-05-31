import { Button } from '../../../shared/components/Button'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import { Textarea } from '../../../shared/components/Textarea'
import { SettingsPanel } from '../../../shared/layout/SplitPane'
import type { BlockDefinition, BlockItemFieldDefinition } from '../data/blockDefinitions'
import type { PageContent } from '../schemas/page'

type BlockInspectorProps = {
  block: PageContent['blocks'][number] | null
  onChange: (blockId: string, props: Record<string, unknown>) => void
  getBlockDefinition: (type: string) => BlockDefinition | undefined
}

type ItemRecord = Record<string, string>

function readItems(value: unknown): ItemRecord[] {
  if (!Array.isArray(value)) {
    return []
  }

  return value.filter((item): item is ItemRecord => typeof item === 'object' && item !== null) as ItemRecord[]
}

function ItemListEditor({
  label,
  items,
  itemFields,
  onChange,
}: {
  label: string
  items: ItemRecord[]
  itemFields: BlockItemFieldDefinition[]
  onChange: (items: ItemRecord[]) => void
}) {
  function updateItem(index: number, field: string, value: string) {
    const next = items.map((item, itemIndex) =>
      itemIndex === index ? { ...item, [field]: value } : item,
    )
    onChange(next)
  }

  function addItem() {
    const emptyItem = Object.fromEntries(itemFields.map((field) => [field.name, '']))
    onChange([...items, emptyItem])
  }

  function removeItem(index: number) {
    onChange(items.filter((_, itemIndex) => itemIndex !== index))
  }

  return (
    <div className="space-y-3">
      <div className="flex items-center justify-between gap-2">
        <p className="text-sm font-medium text-slate-900">{label}</p>
        <Button type="button" variant="ghost" onClick={addItem}>
          Add item
        </Button>
      </div>
      {items.length === 0 && (
        <p className="text-sm text-slate-500">No items yet. Add at least one.</p>
      )}
      {items.map((item, index) => (
        <div key={index} className="space-y-3 rounded-lg border border-slate-200 p-3">
          <div className="flex items-center justify-between gap-2">
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Item {index + 1}
            </p>
            <Button type="button" variant="ghost" onClick={() => removeItem(index)}>
              Remove
            </Button>
          </div>
          {itemFields.map((field) => {
            const value = item[field.name] ?? ''

            if (field.type === 'textarea') {
              return (
                <Textarea
                  key={field.name}
                  label={field.label}
                  id={`item-${index}-${field.name}`}
                  rows={3}
                  value={value}
                  onChange={(event) => updateItem(index, field.name, event.target.value)}
                />
              )
            }

            return (
              <Input
                key={field.name}
                label={field.label}
                id={`item-${index}-${field.name}`}
                value={value}
                onChange={(event) => updateItem(index, field.name, event.target.value)}
              />
            )
          })}
        </div>
      ))}
    </div>
  )
}

export function BlockInspector({ block, onChange, getBlockDefinition }: BlockInspectorProps) {
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

  function updateProp(name: string, value: unknown) {
    if (!block) {
      return
    }

    onChange(block.id, {
      ...block.props,
      [name]: value,
    })
  }

  function updateStringProp(name: string, value: string) {
    updateProp(name, value)
  }

  return (
    <SettingsPanel title={definition.label}>
      <HelpText className="mb-4">{definition.description}</HelpText>
      <div className="space-y-4">
        {definition.fields.map((field) => {
          if (field.type === 'item_list' && field.itemFields) {
            return (
              <ItemListEditor
                key={field.name}
                label={field.label}
                items={readItems(block.props[field.name])}
                itemFields={field.itemFields}
                onChange={(items) => updateProp(field.name, items)}
              />
            )
          }

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
                onChange={(event) => updateStringProp(field.name, event.target.value)}
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
              onChange={(event) => updateStringProp(field.name, event.target.value)}
            />
          )
        })}
      </div>
    </SettingsPanel>
  )
}
