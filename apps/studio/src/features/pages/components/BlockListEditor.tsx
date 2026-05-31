type BlockListEditorProps = {
  value: string
  onChange: (value: string) => void
  error?: string | null
}

export function BlockListEditor({ value, onChange, error }: BlockListEditorProps) {
  return (
    <div>
      <label className="mb-1 block text-sm font-medium text-slate-700" htmlFor="page-content-json">
        Content blocks (JSON)
      </label>
      <p className="mb-2 text-xs text-slate-500">
        Allowed block types: hero, rich_text, cta. Each block needs id, type, and props. Media
        references use a uuid string in props (e.g. image_uuid).
      </p>
      <textarea
        id="page-content-json"
        className={`min-h-64 w-full rounded-lg border px-3 py-2 font-mono text-sm ${
          error ? 'border-red-300' : 'border-slate-300'
        }`}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        spellCheck={false}
      />
      {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
    </div>
  )
}
