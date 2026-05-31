import type { PageContent } from '../schemas/page'

export type BlockTemplate = {
  id: string
  label: string
  description: string
  content: PageContent
}

export const blockTemplates: BlockTemplate[] = [
  {
    id: 'landing-hero',
    label: 'Landing hero',
    description: 'Hero headline with optional subheadline',
    content: {
      blocks: [
        {
          id: 'hero-1',
          type: 'hero',
          props: {
            headline: 'Your headline here',
            subheadline: 'Supporting line for your value proposition.',
          },
        },
      ],
    },
  },
  {
    id: 'rich-text',
    label: 'Rich text section',
    description: 'Single text block for body copy',
    content: {
      blocks: [
        {
          id: 'text-1',
          type: 'rich_text',
          props: {
            body: 'Write your page content here.',
          },
        },
      ],
    },
  },
  {
    id: 'cta-button',
    label: 'Call to action',
    description: 'Primary button link',
    content: {
      blocks: [
        {
          id: 'cta-1',
          type: 'cta',
          props: {
            label: 'Get started',
            url: '/contact',
          },
        },
      ],
    },
  },
  {
    id: 'contact-form',
    label: 'Contact form',
    description: 'Embeds the contact form (requires form slug "contact")',
    content: {
      blocks: [
        {
          id: 'contact-1',
          type: 'contact_form',
          props: {
            form_slug: 'contact',
            title: 'Contact us',
            submit_label: 'Send message',
          },
        },
      ],
    },
  },
  {
    id: 'business-landing',
    label: 'Business landing preset',
    description: 'Hero + text + CTA — typical SMB homepage',
    content: {
      blocks: [
        {
          id: 'hero-1',
          type: 'hero',
          props: {
            headline: 'Welcome to our business',
            subheadline: 'We help customers solve real problems.',
          },
        },
        {
          id: 'text-1',
          type: 'rich_text',
          props: {
            body: 'Tell visitors who you are, what you offer, and why they should trust you.',
          },
        },
        {
          id: 'cta-1',
          type: 'cta',
          props: {
            label: 'Contact us',
            url: '/contact',
          },
        },
      ],
    },
  },
]

export const defaultLandingContentJson = JSON.stringify(
  blockTemplates.find((template) => template.id === 'business-landing')!.content,
  null,
  2,
)
