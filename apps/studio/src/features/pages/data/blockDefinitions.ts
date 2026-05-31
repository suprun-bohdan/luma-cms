import type { PageContent } from '../schemas/page'

export type BlockFieldType = 'text' | 'textarea' | 'url'

export type BlockFieldDefinition = {
  name: string
  label: string
  type: BlockFieldType
  placeholder?: string
}

export type BlockType = PageContent['blocks'][number]['type']

export type BlockDefinition = {
  type: BlockType
  label: string
  description: string
  defaultProps: Record<string, unknown>
  fields: BlockFieldDefinition[]
}

export const blockDefinitions: BlockDefinition[] = [
  {
    type: 'hero',
    label: 'Hero',
    description: 'Headline section with optional subheadline',
    defaultProps: {
      headline: 'Your headline here',
      subheadline: 'Supporting line for your value proposition.',
    },
    fields: [
      { name: 'headline', label: 'Headline', type: 'text' },
      { name: 'subheadline', label: 'Subheadline', type: 'text' },
      {
        name: 'image_uuid',
        label: 'Image media UUID',
        type: 'text',
        placeholder: '00000000-0000-0000-0000-000000000000',
      },
    ],
  },
  {
    type: 'rich_text',
    label: 'Rich text',
    description: 'Body copy paragraph',
    defaultProps: {
      body: 'Write your page content here.',
    },
    fields: [{ name: 'body', label: 'Body', type: 'textarea' }],
  },
  {
    type: 'cta',
    label: 'Call to action',
    description: 'Primary button link',
    defaultProps: {
      label: 'Get started',
      url: '/contact',
    },
    fields: [
      { name: 'label', label: 'Button label', type: 'text' },
      { name: 'url', label: 'URL', type: 'url' },
    ],
  },
  {
    type: 'contact_form',
    label: 'Contact form',
    description: 'Embeds a form by slug (e.g. contact)',
    defaultProps: {
      form_slug: 'contact',
      title: 'Contact us',
      submit_label: 'Send message',
    },
    fields: [
      { name: 'form_slug', label: 'Form slug', type: 'text' },
      { name: 'title', label: 'Heading', type: 'text' },
      { name: 'submit_label', label: 'Submit button label', type: 'text' },
    ],
  },
]

export function getBlockDefinition(type: BlockType): BlockDefinition | undefined {
  return blockDefinitions.find((definition) => definition.type === type)
}

export function createBlock(type: BlockType, index: number): PageContent['blocks'][number] {
  const definition = getBlockDefinition(type)
  if (!definition) {
    throw new Error(`Unknown block type: ${type}`)
  }

  return {
    id: `${type}-${index + 1}`,
    type,
    props: { ...definition.defaultProps },
  }
}

export function createUniqueBlockId(type: BlockType, existingIds: Set<string>): string {
  let index = 1
  let id = `${type}-${index}`

  while (existingIds.has(id)) {
    index += 1
    id = `${type}-${index}`
  }

  return id
}
