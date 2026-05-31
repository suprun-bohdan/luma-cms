import { z } from 'zod'
import { apiGetList } from '../../../shared/api/client'

const blockFieldSchema = z.object({
  name: z.string(),
  label: z.string(),
  type: z.string(),
  placeholder: z.string().optional(),
  item_fields: z
    .array(
      z.object({
        name: z.string(),
        label: z.string(),
        type: z.string(),
      }),
    )
    .optional(),
})

export const editorBlockTypeSchema = z.object({
  type: z.string(),
  label: z.string(),
  description: z.string(),
  category: z.enum(['content', 'actions', 'forms', 'plugins']),
  default_props: z.record(z.string(), z.unknown()),
  fields: z.array(blockFieldSchema),
  source: z.enum(['core', 'plugin']),
  plugin_id: z.string().optional(),
})

export type EditorBlockType = z.infer<typeof editorBlockTypeSchema>

export async function listEditorBlockTypes(): Promise<EditorBlockType[]> {
  return apiGetList('/api/v1/editor/block-types', editorBlockTypeSchema, { auth: true })
}
