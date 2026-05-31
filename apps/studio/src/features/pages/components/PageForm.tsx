import { useMemo, useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { Fieldset } from '../../../shared/components/Fieldset'
import { Input } from '../../../shared/components/Input'
import { Textarea } from '../../../shared/components/Textarea'
import { PreviewPane, SplitPane } from '../../../shared/layout/SplitPane'
import { slugify } from '../../../shared/utils/format'
import { BlockInspector } from './BlockInspector'
import { BlockListEditor } from './BlockListEditor'
import { BlockPalette } from './BlockPalette'
import { PagePreviewPane } from './PagePreviewPane'
import { SeoPreviewPane } from './SeoPreviewPane'
import { VisualBlockList } from './VisualBlockList'
import { createUniqueBlockId, getBlockDefinition } from '../data/blockDefinitions'
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
  previewEnabled?: boolean
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
  previewEnabled = true,
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
  const [seoTitle, setSeoTitle] = useState(initialValues?.seoTitle ?? '')
  const [seoDescription, setSeoDescription] = useState(initialValues?.seoDescription ?? '')
  const [seoOgImage, setSeoOgImage] = useState(initialValues?.seoOgImage ?? '')
  const [formError, setFormError] = useState<string | null>(null)
  const [contentError, setContentError] = useState<string | null>(null)

  const selectedBlock = useMemo(
    () => content.blocks.find((block) => block.id === selectedBlockId) ?? null,
    [content.blocks, selectedBlockId],
  )

  const contentJson = useMemo(() => JSON.stringify(content, null, 2), [content])

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
          props: { ...definition.defaultProps },
        },
      ],
    }

    setContent(nextContent)
    setSelectedBlockId(id)
  }

  function handleBlockPropsChange(blockId: string, props: Record<string, unknown>) {
    setContent({
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
        <BlockPalette onAdd={handleAddBlock} />
        <div className="mt-4">
          <VisualBlockList
            content={content}
            selectedBlockId={selectedBlockId}
            onSelect={setSelectedBlockId}
            onChange={setContent}
          />
        </div>
        <div className="mt-4">
          <Button
            type="button"
            variant="ghost"
            onClick={() => setShowAdvancedJson((current) => !current)}
          >
            {showAdvancedJson ? 'Hide advanced JSON' : 'Show advanced JSON'}
          </Button>
        </div>
        {showAdvancedJson && (
          <div className="mt-3">
            <BlockListEditor
              value={contentJson}
              onChange={(value) => {
                try {
                  setContent(parseContentJson(value))
                } catch {
                  // Invalid JSON stays in textarea until fixed.
                }
              }}
              error={contentError}
            />
          </div>
        )}
      </Fieldset>

      <Fieldset legend="SEO">
        <Input
          label="Meta title (max 70)"
          id="seo-title"
          value={seoTitle}
          maxLength={70}
          onChange={(event) => setSeoTitle(event.target.value)}
        />

        <Textarea
          label="Meta description (max 160)"
          id="seo-description"
          value={seoDescription}
          maxLength={160}
          rows={3}
          onChange={(event) => setSeoDescription(event.target.value)}
        />

        <Input
          label="OG image media uuid"
          id="seo-og-image"
          value={seoOgImage}
          placeholder="00000000-0000-0000-0000-000000000000"
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
      <BlockInspector block={selectedBlock} onChange={handleBlockPropsChange} />
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
          enabled={previewEnabled}
        />
      }
      right={rightColumn}
    />
  )
}
