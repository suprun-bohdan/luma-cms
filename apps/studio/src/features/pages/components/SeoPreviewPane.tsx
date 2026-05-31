type SeoPreviewPaneProps = {
  pageTitle: string
  pageSlug: string
  seoTitle: string
  seoDescription: string
  seoOgImage: string
}

function resolveTitle(pageTitle: string, seoTitle: string): string {
  return seoTitle.trim() !== '' ? seoTitle.trim() : pageTitle.trim()
}

export function SeoPreviewPane({
  pageTitle,
  pageSlug,
  seoTitle,
  seoDescription,
  seoOgImage,
}: SeoPreviewPaneProps) {
  const title = resolveTitle(pageTitle, seoTitle)
  const titleLength = title.length
  const descriptionLength = seoDescription.trim().length
  const slugPath = pageSlug.trim() !== '' ? `/p/${pageSlug.trim()}` : '/p/your-page-slug'

  const titleWarning =
    titleLength === 0 ? 'Missing title' : titleLength > 70 ? 'Title exceeds 70 characters' : null
  const descriptionWarning =
    descriptionLength === 0
      ? 'Missing description'
      : descriptionLength > 160
        ? 'Description exceeds 160 characters'
        : null

  return (
    <div className="space-y-4 text-sm">
      <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p className="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">
          Search preview
        </p>
        <p className="truncate text-lg text-blue-700">{title || 'Page title'}</p>
        <p className="truncate text-xs text-emerald-700">{slugPath}</p>
        <p className="mt-1 line-clamp-2 text-slate-600">
          {seoDescription.trim() || 'Meta description will appear here.'}
        </p>
      </div>

      <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <p className="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">
          Open Graph
        </p>
        <p className="font-medium text-slate-900">{title || 'Page title'}</p>
        <p className="mt-1 text-slate-600">{seoDescription.trim() || 'No description set.'}</p>
        {seoOgImage.trim() !== '' ? (
          <p className="mt-2 truncate font-mono text-xs text-slate-500">og:image: {seoOgImage}</p>
        ) : (
          <p className="mt-2 text-xs text-amber-700">No OG image set.</p>
        )}
      </div>

      <ul className="space-y-1 text-xs text-slate-600">
        <li>
          Title: {titleLength}/70
          {titleWarning && <span className="ml-2 text-amber-700">{titleWarning}</span>}
        </li>
        <li>
          Description: {descriptionLength}/160
          {descriptionWarning && <span className="ml-2 text-amber-700">{descriptionWarning}</span>}
        </li>
      </ul>

      <p className="text-xs text-slate-500">
        Sitemap: <a className="underline" href="/sitemap.xml" target="_blank" rel="noreferrer">/sitemap.xml</a>
      </p>
    </div>
  )
}
