import { Button } from '../../../shared/components/Button'
import { getBlockDefinition } from '../data/blockDefinitions'
import type { PageContent } from '../schemas/page'

type VisualBlockListProps = {
  content: PageContent
  selectedBlockId: string | null
  onSelect: (blockId: string) => void
  onChange: (content: PageContent) => void
}

export function VisualBlockList({
  content,
  selectedBlockId,
  onSelect,
  onChange,
}: VisualBlockListProps) {
  function moveBlock(index: number, direction: -1 | 1) {
    const targetIndex = index + direction
    if (targetIndex < 0 || targetIndex >= content.blocks.length) {
      return
    }

    const blocks = [...content.blocks]
    const [item] = blocks.splice(index, 1)
    blocks.splice(targetIndex, 0, item)
    onChange({ blocks })
  }

  function removeBlock(index: number) {
    const blocks = content.blocks.filter((_, blockIndex) => blockIndex !== index)
    onChange({ blocks })
  }

  if (content.blocks.length === 0) {
    return (
      <p className="rounded-lg border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">
        No blocks yet. Add a section from the palette above.
      </p>
    )
  }

  return (
    <div className="space-y-2">
      {content.blocks.map((block, index) => {
        const definition = getBlockDefinition(block.type)
        const isSelected = block.id === selectedBlockId

        return (
          <div
            key={block.id}
            className={`rounded-lg border p-3 ${
              isSelected ? 'border-slate-900 bg-slate-50' : 'border-slate-200 bg-white'
            }`}
          >
            <button
              type="button"
              className="flex w-full items-start justify-between gap-3 text-left"
              onClick={() => onSelect(block.id)}
            >
              <div>
                <p className="text-sm font-medium text-slate-900">
                  {definition?.label ?? block.type}
                </p>
                <p className="mt-0.5 font-mono text-xs text-slate-500">{block.id}</p>
              </div>
              <span className="text-xs uppercase tracking-wide text-slate-400">{block.type}</span>
            </button>

            <div className="mt-3 flex flex-wrap gap-2">
              <Button
                type="button"
                variant="ghost"
                disabled={index === 0}
                onClick={() => moveBlock(index, -1)}
              >
                Up
              </Button>
              <Button
                type="button"
                variant="ghost"
                disabled={index === content.blocks.length - 1}
                onClick={() => moveBlock(index, 1)}
              >
                Down
              </Button>
              <Button type="button" variant="ghost" onClick={() => removeBlock(index)}>
                Remove
              </Button>
            </div>
          </div>
        )
      })}
    </div>
  )
}
