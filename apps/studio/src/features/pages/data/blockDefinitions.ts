import type { PageContent } from '../schemas/page'

export type BlockFieldType = 'text' | 'textarea' | 'url' | 'item_list'

export type BlockItemFieldDefinition = {
  name: string
  label: string
  type: 'text' | 'textarea'
}

export type BlockFieldDefinition = {
  name: string
  label: string
  type: BlockFieldType
  placeholder?: string
  itemFields?: BlockItemFieldDefinition[]
}

export type BlockType = string

export type BlockDefinition = {
  type: BlockType
  label: string
  description: string
  category: 'content' | 'actions' | 'forms' | 'plugins'
  defaultProps: Record<string, unknown>
  fields: BlockFieldDefinition[]
}

export const blockDefinitions: BlockDefinition[] = [
  {
    type: 'hero',
    label: 'Hero',
    description: 'Headline section with optional subheadline',
    category: 'content',
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
    category: 'content',
    defaultProps: {
      body: 'Write your page content here.',
    },
    fields: [{ name: 'body', label: 'Body', type: 'textarea' }],
  },
  {
    type: 'feature_grid',
    label: 'Feature grid',
    description: 'Heading with a list of feature items',
    category: 'content',
    defaultProps: {
      heading: 'Why choose us',
      items: [
        { title: 'Fast', body: 'Launch pages quickly with structured blocks.' },
        { title: 'Flexible', body: 'Compose layouts without losing control.' },
      ],
    },
    fields: [
      { name: 'heading', label: 'Heading', type: 'text' },
      {
        name: 'items',
        label: 'Features',
        type: 'item_list',
        itemFields: [
          { name: 'title', label: 'Title', type: 'text' },
          { name: 'body', label: 'Body', type: 'textarea' },
        ],
      },
    ],
  },
  {
    type: 'faq',
    label: 'FAQ',
    description: 'Heading with question and answer pairs',
    category: 'content',
    defaultProps: {
      heading: 'Frequently asked questions',
      items: [
        {
          question: 'What is Luma CMS?',
          answer: 'A modular CMS with structured content and a visual page editor.',
        },
      ],
    },
    fields: [
      { name: 'heading', label: 'Heading', type: 'text' },
      {
        name: 'items',
        label: 'Questions',
        type: 'item_list',
        itemFields: [
          { name: 'question', label: 'Question', type: 'text' },
          { name: 'answer', label: 'Answer', type: 'textarea' },
        ],
      },
    ],
  },
  {
    type: 'cta',
    label: 'Call to action',
    description: 'Primary button link',
    category: 'actions',
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
    category: 'forms',
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

export const blockPaletteGroups: {
  id: BlockDefinition['category']
  label: string
  description: string
}[] = [
  {
    id: 'content',
    label: 'Content',
    description: 'Headlines, copy, features, and FAQs',
  },
  {
    id: 'actions',
    label: 'Actions',
    description: 'Buttons and conversion sections',
  },
  {
    id: 'forms',
    label: 'Forms',
    description: 'Embedded forms from the Forms module',
  },
]

export function getBlockDefinition(type: BlockType): BlockDefinition | undefined {
  return blockDefinitions.find((definition) => definition.type === type)
}

export function getBlocksByCategory(category: BlockDefinition['category']): BlockDefinition[] {
  return blockDefinitions.filter((definition) => definition.category === category)
}

export function createBlock(type: BlockType, index: number): PageContent['blocks'][number] {
  const definition = getBlockDefinition(type)
  if (!definition) {
    throw new Error(`Unknown block type: ${type}`)
  }

  return {
    id: `${type}-${index + 1}`,
    type,
    props: JSON.parse(JSON.stringify(definition.defaultProps)) as Record<string, unknown>,
  }
}

export function createUniqueBlockId(type: BlockType, existingIds: Set<string>): string {
  const base = type.replace(/\//g, '-')
  let index = 1
  let id = `${base}-${index}`

  while (existingIds.has(id)) {
    index += 1
    id = `${base}-${index}`
  }

  return id
}
