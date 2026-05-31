import { useEffect, useMemo, useState } from 'react'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PreviewPane } from '../../../shared/layout/SplitPane'
import { previewPageHtml } from '../api/pagesApi'
import type { PageContent, PageSeo } from '../schemas/page'

type PagePreviewPaneProps = {
  pageSlug: string
  title: string
  content: PageContent
  seo: PageSeo | null
  enabled: boolean
}

export function PagePreviewPane({
  pageSlug,
  title,
  content,
  seo,
  enabled,
}: PagePreviewPaneProps) {
  const [html, setHtml] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const canPreview = enabled && pageSlug.trim() !== ''

  const previewPayload = useMemo(
    () => JSON.stringify({ title, content, seo }),
    [title, content, seo],
  )

  useEffect(() => {
    if (!canPreview) {
      return
    }

    const timer = window.setTimeout(() => {
      setLoading(true)
      setError(null)

      void previewPageHtml(pageSlug, {
        title,
        content,
        seo,
      })
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
  }, [canPreview, pageSlug, previewPayload, title, content, seo])

  if (!canPreview) {
    return (
      <PreviewPane title="Page preview">
        <p className="text-sm text-slate-500">
          Save the page with a slug to enable live server-side preview.
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
