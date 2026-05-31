import { Textarea } from '../../../shared/components/Textarea'

type BlockListEditorProps = {
  value: string
  onChange: (value: string) => void
  error?: string | null
}

export function BlockListEditor({ value, onChange, error }: BlockListEditorProps) {
  return (
    <div>
      <p className="mb-2 text-xs text-slate-500">
        Allowed block types: hero, rich_text, cta. Each block needs id, type, and props. Media
        references use a uuid string in props (e.g. image_uuid).
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
