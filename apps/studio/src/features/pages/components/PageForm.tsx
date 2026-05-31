import { useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { Fieldset } from '../../../shared/components/Fieldset'
import { Input } from '../../../shared/components/Input'
import { Textarea } from '../../../shared/components/Textarea'
import { PreviewPane, SplitPane } from '../../../shared/layout/SplitPane'
import { slugify } from '../../../shared/utils/format'
import { BlockListEditor } from './BlockListEditor'
import { SeoPreviewPane } from './SeoPreviewPane'
import {
  defaultContentJson,
  pageFormSchema,
  type PageFormValues,
} from '../schemas/page'

type PageFormProps = {
  initialValues?: Partial<PageFormValues>
  submitLabel: string
  loading?: boolean
  onSubmit: (values: PageFormValues) => void
}

export function PageForm({
  initialValues,
  submitLabel,
  loading = false,
  onSubmit,
}: PageFormProps) {
  const [title, setTitle] = useState(initialValues?.title ?? '')
  const [slug, setSlug] = useState(initialValues?.slug ?? '')
  const [slugTouched, setSlugTouched] = useState(Boolean(initialValues?.slug))
  const [contentJson, setContentJson] = useState(
    initialValues?.contentJson ?? defaultContentJson,
  )
  const [seoTitle, setSeoTitle] = useState(initialValues?.seoTitle ?? '')
  const [seoDescription, setSeoDescription] = useState(initialValues?.seoDescription ?? '')
  const [seoOgImage, setSeoOgImage] = useState(initialValues?.seoOgImage ?? '')
  const [formError, setFormError] = useState<string | null>(null)
  const [contentError, setContentError] = useState<string | null>(null)

  function handleTitleChange(value: string) {
    setTitle(value)
    if (!slugTouched) {
      setSlug(slugify(value))
    }
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

  const formFields = (
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

      <BlockListEditor value={contentJson} onChange={setContentJson} error={contentError} />

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

  return (
    <SplitPane
      left={formFields}
      right={
        <PreviewPane title="SEO preview">
          <SeoPreviewPane
            pageTitle={title}
            pageSlug={slug}
            seoTitle={seoTitle}
            seoDescription={seoDescription}
            seoOgImage={seoOgImage}
          />
        </PreviewPane>
      }
    />
  )
}
