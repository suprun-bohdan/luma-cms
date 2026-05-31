import { useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { slugify } from '../../../shared/utils/format'
import { BlockListEditor } from './BlockListEditor'
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

  return (
    <form className="space-y-6" onSubmit={handleSubmit}>
      {formError && <p className="text-sm text-red-600">{formError}</p>}

      <div>
        <label className="mb-1 block text-sm font-medium text-slate-700" htmlFor="page-title">
          Title
        </label>
        <input
          id="page-title"
          className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
          value={title}
          onChange={(event) => handleTitleChange(event.target.value)}
          required
        />
      </div>

      <div>
        <label className="mb-1 block text-sm font-medium text-slate-700" htmlFor="page-slug">
          Slug
        </label>
        <input
          id="page-slug"
          className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
          value={slug}
          onChange={(event) => {
            setSlugTouched(true)
            setSlug(event.target.value)
          }}
          required
        />
      </div>

      <BlockListEditor
        value={contentJson}
        onChange={setContentJson}
        error={contentError}
      />

      <fieldset className="space-y-4 rounded-lg border border-slate-200 p-4">
        <legend className="px-1 text-sm font-semibold text-slate-900">SEO</legend>

        <div>
          <label className="mb-1 block text-sm font-medium text-slate-700" htmlFor="seo-title">
            Meta title (max 70)
          </label>
          <input
            id="seo-title"
            className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            value={seoTitle}
            maxLength={70}
            onChange={(event) => setSeoTitle(event.target.value)}
          />
        </div>

        <div>
          <label
            className="mb-1 block text-sm font-medium text-slate-700"
            htmlFor="seo-description"
          >
            Meta description (max 160)
          </label>
          <textarea
            id="seo-description"
            className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            value={seoDescription}
            maxLength={160}
            rows={3}
            onChange={(event) => setSeoDescription(event.target.value)}
          />
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium text-slate-700" htmlFor="seo-og-image">
            OG image media uuid
          </label>
          <input
            id="seo-og-image"
            className="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm"
            value={seoOgImage}
            placeholder="00000000-0000-0000-0000-000000000000"
            onChange={(event) => setSeoOgImage(event.target.value)}
          />
        </div>
      </fieldset>

      <Button type="submit" disabled={loading}>
        {loading ? 'Saving…' : submitLabel}
      </Button>
    </form>
  )
}
