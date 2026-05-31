import { Button } from '../../../shared/components/Button'
import { getBlocksByCategory, type BlockDefinition } from '../data/blockDefinitions'
import { useBlockPaletteGroups } from '../hooks/useBlockTypes'

type BlockPaletteProps = {
  definitions: BlockDefinition[]
  onAdd: (type: BlockDefinition['type']) => void
}

export function BlockPalette({ definitions, onAdd }: BlockPaletteProps) {
  const groups = useBlockPaletteGroups(definitions)

  return (
    <div className="space-y-4">
      {groups.map((group) => {
        const blocks = getBlocksByCategory(group.id).length
          ? getBlocksByCategory(group.id)
          : definitions.filter((definition) => definition.category === group.id)

        if (blocks.length === 0) {
          return null
        }

        return (
          <div key={group.id}>
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{group.label}</p>
            <p className="mt-0.5 text-xs text-slate-500">{group.description}</p>
            <div className="mt-2 flex flex-wrap gap-2">
              {blocks.map((definition) => (
                <Button
                  key={definition.type}
                  type="button"
                  variant="secondary"
                  title={definition.description}
                  onClick={() => onAdd(definition.type)}
                >
                  + {definition.label}
                  {definition.category === 'plugins' ? ' (Plugin)' : ''}
                </Button>
              ))}
            </div>
          </div>
        )
      })}
    </div>
  )
}
