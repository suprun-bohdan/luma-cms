import { Button } from '../../../shared/components/Button'
import { cloneSectionTemplateContent, sectionTemplates } from '../data/sectionTemplates'
import { createUniqueBlockId } from '../data/blockDefinitions'
import type { PageContent } from '../schemas/page'

type SectionTemplatePickerProps = {
  content: PageContent
  onApply: (content: PageContent) => void
}

export function SectionTemplatePicker({ content, onApply }: SectionTemplatePickerProps) {
  function applyTemplate(templateId: string) {
    const template = sectionTemplates.find((item) => item.id === templateId)
    if (!template) {
      return
    }

    if (content.blocks.length > 0) {
      const confirmed = window.confirm(
        'Replace all current blocks with this section template? This cannot be undone until you save.',
      )
      if (!confirmed) {
        return
      }
    }

    const nextContent = cloneSectionTemplateContent(template)
    const existingIds = new Set<string>()

    nextContent.blocks = nextContent.blocks.map((block) => {
      const id = createUniqueBlockId(block.type, existingIds)
      existingIds.add(id)
      return { ...block, id }
    })

    onApply(nextContent)
  }

  return (
    <div className="space-y-2">
      <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Section templates</p>
      <div className="flex flex-wrap gap-2">
        {sectionTemplates.map((template) => (
          <Button
            key={template.id}
            type="button"
            variant="ghost"
            title={template.description}
            onClick={() => applyTemplate(template.id)}
          >
            {template.label}
          </Button>
        ))}
      </div>
    </div>
  )
}
