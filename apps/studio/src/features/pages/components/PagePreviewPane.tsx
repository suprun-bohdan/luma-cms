import { useEffect, useMemo, useState } from 'react'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PreviewPane } from '../../../shared/layout/SplitPane'
import { previewDraftPageHtml, previewPageHtml } from '../api/pagesApi'
import type { PageContent, PageSeo } from '../schemas/page'

const slugPattern = /^[a-z0-9-]+$/

type PagePreviewPaneProps = {
  pageSlug: string
  title: string
  content: PageContent
  seo: PageSeo | null
  isNew?: boolean
}

export function PagePreviewPane({
  pageSlug,
  title,
  content,
  seo,
  isNew = false,
}: PagePreviewPaneProps) {
  const [html, setHtml] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const slug = pageSlug.trim()
  const canPreview =
    slug !== '' &&
    slugPattern.test(slug) &&
    title.trim() !== ''

  const previewPayload = useMemo(
    () => JSON.stringify({ slug, title, content, seo, isNew }),
    [slug, title, content, seo, isNew],
  )

  useEffect(() => {
    if (!canPreview) {
      return
    }

    const timer = window.setTimeout(() => {
      setLoading(true)
      setError(null)

      const request = isNew
        ? previewDraftPageHtml({ slug, title, content, seo })
        : previewPageHtml(slug, { title, content, seo })

      void request
        .then((response) => {
          setHtml(response.html)
        })
        .catch((previewError: unknown) => {
          setHtml(null)
          setError(previewError instanceof Error ? previewError.message : 'Preview failed')
        })
        .finally(() => {
          setLoading(false)
        })
    }, 500)

    return () => {
      window.clearTimeout(timer)
    }
  }, [canPreview, slug, title, content, seo, isNew, previewPayload])

  if (!canPreview) {
    return (
      <PreviewPane title="Page preview">
        <p className="text-sm text-slate-500">
          Enter a title and valid slug (lowercase letters, numbers, hyphens) to enable live preview.
        </p>
      </PreviewPane>
    )
  }

  return (
    <PreviewPane title="Page preview">
      {loading && <LoadingState message="Rendering preview…" />}
      {error && <ErrorAlert message={error} />}
      {!loading && !error && html && (
        <iframe
          title="Page preview"
          className="h-[32rem] w-full rounded-lg border border-slate-200 bg-white"
          sandbox=""
          srcDoc={html}
        />
      )}
    </PreviewPane>
  )
}
