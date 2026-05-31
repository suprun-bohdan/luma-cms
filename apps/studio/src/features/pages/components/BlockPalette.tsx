import { Button } from '../../../shared/components/Button'
import { blockDefinitions } from '../data/blockDefinitions'

type BlockPaletteProps = {
  onAdd: (type: (typeof blockDefinitions)[number]['type']) => void
}

export function BlockPalette({ onAdd }: BlockPaletteProps) {
  return (
    <div className="flex flex-wrap gap-2">
      {blockDefinitions.map((definition) => (
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
  )
}
