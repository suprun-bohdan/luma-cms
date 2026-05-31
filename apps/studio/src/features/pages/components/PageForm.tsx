import { useMemo, useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { Fieldset } from '../../../shared/components/Fieldset'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import { Textarea } from '../../../shared/components/Textarea'
import { PreviewPane, SplitPane } from '../../../shared/layout/SplitPane'
import { slugify } from '../../../shared/utils/format'
import { BlockInspector } from './BlockInspector'
import { BlockListEditor } from './BlockListEditor'
import { BlockPalette } from './BlockPalette'
import { PagePreviewPane } from './PagePreviewPane'
import { SectionTemplatePicker } from './SectionTemplatePicker'
import { SeoPreviewPane } from './SeoPreviewPane'
import { VisualBlockList } from './VisualBlockList'
import { createUniqueBlockId } from '../data/blockDefinitions'
import { useBlockTypes } from '../hooks/useBlockTypes'
import {
  defaultContentJson,
  pageContentSchema,
  pageFormSchema,
  type PageContent,
  type PageFormValues,
  type PageSeo,
} from '../schemas/page'

type PageFormProps = {
  initialValues?: Partial<PageFormValues>
  isNew?: boolean
  submitLabel: string
  loading?: boolean
  onSubmit: (values: PageFormValues) => void
}

function parseContentJson(value: string): PageContent {
  try {
    const parsed = JSON.parse(value) as unknown
    return pageContentSchema.parse(parsed)
  } catch {
    return pageContentSchema.parse(JSON.parse(defaultContentJson))
  }
}

function buildSeo(seoTitle: string, seoDescription: string, seoOgImage: string): PageSeo | null {
  const seo: PageSeo = {}

  if (seoTitle) {
    seo.title = seoTitle
  }
  if (seoDescription) {
    seo.description = seoDescription
  }
  if (seoOgImage) {
    seo.og_image = seoOgImage
  }

  return Object.keys(seo).length > 0 ? seo : null
}

export function PageForm({
  initialValues,
  isNew = false,
  submitLabel,
  loading = false,
  onSubmit,
}: PageFormProps) {
  const [title, setTitle] = useState(initialValues?.title ?? '')
  const [slug, setSlug] = useState(initialValues?.slug ?? '')
  const [slugTouched, setSlugTouched] = useState(Boolean(initialValues?.slug))
  const [content, setContent] = useState<PageContent>(() =>
    parseContentJson(initialValues?.contentJson ?? defaultContentJson),
  )
  const [selectedBlockId, setSelectedBlockId] = useState<string | null>(
    () => parseContentJson(initialValues?.contentJson ?? defaultContentJson).blocks[0]?.id ?? null,
  )
  const [showAdvancedJson, setShowAdvancedJson] = useState(false)
  const [advancedJsonDraft, setAdvancedJsonDraft] = useState<string | null>(null)
  const [seoTitle, setSeoTitle] = useState(initialValues?.seoTitle ?? '')
  const [seoDescription, setSeoDescription] = useState(initialValues?.seoDescription ?? '')
  const [seoOgImage, setSeoOgImage] = useState(initialValues?.seoOgImage ?? '')
  const [formError, setFormError] = useState<string | null>(null)
  const [contentError, setContentError] = useState<string | null>(null)
  const blockTypesQuery = useBlockTypes()
  const { getBlockDefinition, definitions: blockDefinitionsList } = blockTypesQuery

  const selectedBlock = useMemo(
    () => content.blocks.find((block) => block.id === selectedBlockId) ?? null,
    [content.blocks, selectedBlockId],
  )

  const contentJson = useMemo(() => JSON.stringify(content, null, 2), [content])
  const advancedJsonValue = advancedJsonDraft ?? contentJson

  function updateContent(next: PageContent) {
    setContent(next)
    setAdvancedJsonDraft(null)
    setContentError(null)
  }

  function handleAdvancedJsonChange(value: string) {
    setAdvancedJsonDraft(value)

    try {
      const parsed = JSON.parse(value) as unknown
      updateContent(pageContentSchema.parse(parsed))
    } catch {
      setContentError('Invalid JSON or block schema.')
    }
  }

  function toggleAdvancedJson() {
    setShowAdvancedJson((current) => {
      if (!current) {
        setAdvancedJsonDraft(contentJson)
        setContentError(null)
      } else {
        setAdvancedJsonDraft(null)
      }

      return !current
    })
  }

  function handleTitleChange(value: string) {
    setTitle(value)
    if (!slugTouched) {
      setSlug(slugify(value))
    }
  }

  function handleAddBlock(type: PageContent['blocks'][number]['type']) {
    const definition = getBlockDefinition(type)
    if (!definition) {
      return
    }

    const existingIds = new Set(content.blocks.map((block) => block.id))
    const id = createUniqueBlockId(type, existingIds)

    const nextContent: PageContent = {
      blocks: [
        ...content.blocks,
        {
          id,
          type,
          props: JSON.parse(JSON.stringify(definition.defaultProps)) as Record<string, unknown>,
        },
      ],
    }

    setContent(nextContent)
    setAdvancedJsonDraft(null)
    setSelectedBlockId(id)
  }

  function handleBlockPropsChange(blockId: string, props: Record<string, unknown>) {
    updateContent({
      blocks: content.blocks.map((block) =>
        block.id === blockId ? { ...block, props } : block,
      ),
    })
  }

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()
    setFormError(null)
    setContentError(null)

    const parsed = pageFormSchema.safeParse({
      title,
      slug,
      template: 'default-page',
      contentJson,
      seoTitle: seoTitle || undefined,
      seoDescription: seoDescription || undefined,
      seoOgImage: seoOgImage || undefined,
    })

    if (!parsed.success) {
      const contentIssue = parsed.error.issues.find((issue) => issue.path[0] === 'contentJson')
      if (contentIssue) {
        setContentError(contentIssue.message)
      }
      setFormError(parsed.error.issues.map((issue) => issue.message).join(' '))
      return
    }

    onSubmit(parsed.data)
  }

  const seo = buildSeo(seoTitle, seoDescription, seoOgImage)

  const leftColumn = (
    <form className="space-y-6" onSubmit={handleSubmit}>
      {formError && <p className="text-sm text-red-600">{formError}</p>}

      <Input
        label="Title"
        id="page-title"
        value={title}
        onChange={(event) => handleTitleChange(event.target.value)}
        required
      />

      <Input
        label="Slug"
        id="page-slug"
        value={slug}
        onChange={(event) => {
          setSlugTouched(true)
          setSlug(event.target.value)
        }}
        required
      />

      <Fieldset legend="Page blocks">
        <HelpText className="mb-3">
          Add sections from the palette below. Select a block to edit its content in the panel on the
          right. Hero, rich text, call to action, and contact form blocks cover most landing pages.
        </HelpText>
        <SectionTemplatePicker
          content={content}
          onApply={(next) => {
            updateContent(next)
            setSelectedBlockId(next.blocks[0]?.id ?? null)
          }}
        />
        <div className="mt-4">
          <BlockPalette definitions={blockDefinitionsList} onAdd={handleAddBlock} />
        </div>
        <div className="mt-4">
          <VisualBlockList
            content={content}
            selectedBlockId={selectedBlockId}
            onSelect={setSelectedBlockId}
            onChange={updateContent}
          />
        </div>
        <div className="mt-4">
          <Button type="button" variant="ghost" onClick={toggleAdvancedJson}>
            {showAdvancedJson ? 'Hide advanced JSON' : 'Show advanced JSON'}
          </Button>
        </div>
        {showAdvancedJson && (
          <div className="mt-3">
            <BlockListEditor
              value={advancedJsonValue}
              onChange={handleAdvancedJsonChange}
              error={contentError}
            />
          </div>
        )}
      </Fieldset>

      <Fieldset legend="SEO">
        <HelpText className="mb-3">
          Optional search and social preview text. Leave blank to use the page title and excerpt from
          content.
        </HelpText>
        <Input
          label="Search title (max 70 characters)"
          id="seo-title"
          value={seoTitle}
          maxLength={70}
          onChange={(event) => setSeoTitle(event.target.value)}
        />

        <Textarea
          label="Search description (max 160 characters)"
          id="seo-description"
          value={seoDescription}
          maxLength={160}
          rows={3}
          onChange={(event) => setSeoDescription(event.target.value)}
        />

        <Input
          label="Social share image (media library UUID)"
          id="seo-og-image"
          value={seoOgImage}
          placeholder="Paste UUID from Media library"
          className="font-mono"
          onChange={(event) => setSeoOgImage(event.target.value)}
        />
      </Fieldset>

      <Button type="submit" disabled={loading}>
        {loading ? 'Saving…' : submitLabel}
      </Button>
    </form>
  )

  const rightColumn = (
    <div className="space-y-4">
      <BlockInspector
        block={selectedBlock}
        onChange={handleBlockPropsChange}
        getBlockDefinition={getBlockDefinition}
      />
      <PreviewPane title="SEO preview">
        <SeoPreviewPane
          pageTitle={title}
          pageSlug={slug}
          seoTitle={seoTitle}
          seoDescription={seoDescription}
          seoOgImage={seoOgImage}
        />
      </PreviewPane>
    </div>
  )

  return (
    <SplitPane
      left={leftColumn}
      center={
        <PagePreviewPane
          pageSlug={slug}
          title={title}
          content={content}
          seo={seo}
          isNew={isNew}
        />
      }
      right={rightColumn}
    />
  )
}
