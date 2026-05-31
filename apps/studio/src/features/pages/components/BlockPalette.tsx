import { Button } from '../../../shared/components/Button'
import { blockPaletteGroups, getBlocksByCategory } from '../data/blockDefinitions'
import type { BlockDefinition } from '../data/blockDefinitions'

type BlockPaletteProps = {
  onAdd: (type: BlockDefinition['type']) => void
}

export function BlockPalette({ onAdd }: BlockPaletteProps) {
  return (
    <div className="space-y-4">
      {blockPaletteGroups.map((group) => {
        const blocks = getBlocksByCategory(group.id)

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
                </Button>
              ))}
            </div>
          </div>
        )
      })}
    </div>
  )
}
