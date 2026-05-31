import { Button } from '../../../shared/components/Button'
import { Textarea } from '../../../shared/components/Textarea'
import { blockTemplates } from '../data/blockTemplates'
import type { PageContent } from '../schemas/page'

type BlockListEditorProps = {
  value: string
  onChange: (value: string) => void
  error?: string | null
}

function mergeBlocks(current: PageContent, template: PageContent): PageContent {
  const existingIds = new Set(current.blocks.map((block) => block.id))
  const nextBlocks = [...current.blocks]

  for (const block of template.blocks) {
    let blockId = block.id
    let suffix = 1
    while (existingIds.has(blockId)) {
      blockId = `${block.id}-${suffix}`
      suffix += 1
    }
    existingIds.add(blockId)
    nextBlocks.push({ ...block, id: blockId })
  }

  return { blocks: nextBlocks }
}

function parseContentJson(value: string): PageContent {
  const parsed = JSON.parse(value) as unknown
  if (
    typeof parsed === 'object' &&
    parsed !== null &&
    Array.isArray((parsed as PageContent).blocks)
  ) {
    return parsed as PageContent
  }

  return { blocks: [] }
}

export function BlockListEditor({ value, onChange, error }: BlockListEditorProps) {
  function applyTemplate(templateId: string) {
    const template = blockTemplates.find((item) => item.id === templateId)
    if (!template) {
      return
    }

    try {
      const current = parseContentJson(value)
      const merged = mergeBlocks(current, template.content)
      onChange(JSON.stringify(merged, null, 2))
    } catch {
      onChange(JSON.stringify(template.content, null, 2))
    }
  }

  function replaceWithTemplate(templateId: string) {
    const template = blockTemplates.find((item) => item.id === templateId)
    if (!template) {
      return
    }

    onChange(JSON.stringify(template.content, null, 2))
  }

  return (
    <div>
      <div className="mb-3 flex flex-wrap gap-2">
        {blockTemplates.map((template) => (
          <Button
            key={template.id}
            type="button"
            variant="secondary"
            onClick={() => applyTemplate(template.id)}
            title={template.description}
          >
            + {template.label}
          </Button>
        ))}
        <Button
          type="button"
          variant="ghost"
          onClick={() => replaceWithTemplate('business-landing')}
        >
          Replace with landing preset
        </Button>
      </div>

      <p className="mb-2 text-xs text-slate-500">
        Allowed block types: hero, rich_text, cta. Each block needs id, type, and props. Media
        references use a uuid string in props (e.g. media_uuid).
      </p>
      <Textarea
        label="Content blocks (JSON)"
        id="page-content-json"
        value={value}
        onChange={(event) => onChange(event.target.value)}
        spellCheck={false}
        error={error ?? undefined}
        className="min-h-64 font-mono"
        rows={12}
      />
    </div>
  )
}
