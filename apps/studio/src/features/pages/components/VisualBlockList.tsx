import { useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { EmptyState } from '../../../shared/components/EmptyState'
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
  const [draggedIndex, setDraggedIndex] = useState<number | null>(null)
  const [dropIndex, setDropIndex] = useState<number | null>(null)

  function reorderBlocks(fromIndex: number, toIndex: number) {
    if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) {
      return
    }

    const blocks = [...content.blocks]
    const [item] = blocks.splice(fromIndex, 1)
    blocks.splice(toIndex, 0, item)
    onChange({ blocks })
  }

  function moveBlock(index: number, direction: -1 | 1) {
    reorderBlocks(index, index + direction)
  }

  function removeBlock(index: number) {
    const removed = content.blocks[index]
    const blocks = content.blocks.filter((_, blockIndex) => blockIndex !== index)
    onChange({ blocks })

    if (removed?.id === selectedBlockId) {
      onSelect(blocks[0]?.id ?? '')
    }
  }

  function handleDragStart(index: number) {
    setDraggedIndex(index)
    setDropIndex(index)
  }

  function handleDragOver(event: React.DragEvent, index: number) {
    event.preventDefault()
    if (draggedIndex === null) {
      return
    }

    setDropIndex(index)
  }

  function handleDrop(index: number) {
    if (draggedIndex === null) {
      return
    }

    reorderBlocks(draggedIndex, index)
    setDraggedIndex(null)
    setDropIndex(null)
  }

  function handleDragEnd() {
    setDraggedIndex(null)
    setDropIndex(null)
  }

  if (content.blocks.length === 0) {
    return (
      <EmptyState
        title="No blocks yet"
        description="Pick a starter section or add a block from the palette above."
      />
    )
  }

  return (
    <div className="space-y-2">
      <p className="text-xs text-slate-500">Drag blocks to reorder, or use Up/Down.</p>
      {content.blocks.map((block, index) => {
        const definition = getBlockDefinition(block.type)
        const isSelected = block.id === selectedBlockId
        const isDragging = draggedIndex === index
        const isDropTarget = dropIndex === index && draggedIndex !== null && draggedIndex !== index

        return (
          <div
            key={block.id}
            draggable
            onDragStart={() => handleDragStart(index)}
            onDragOver={(event) => handleDragOver(event, index)}
            onDrop={() => handleDrop(index)}
            onDragEnd={handleDragEnd}
            className={`rounded-lg border p-3 transition ${
              isSelected ? 'border-slate-900 bg-slate-50' : 'border-slate-200 bg-white'
            } ${isDragging ? 'opacity-50' : ''} ${isDropTarget ? 'ring-2 ring-slate-400' : ''}`}
          >
            <button
              type="button"
              className="flex w-full cursor-grab items-start justify-between gap-3 text-left active:cursor-grabbing"
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
