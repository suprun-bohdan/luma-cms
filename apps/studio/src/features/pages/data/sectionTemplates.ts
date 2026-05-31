import type { PageContent } from '../schemas/page'

export type SectionTemplate = {
  id: string
  label: string
  description: string
  content: PageContent
}

export const sectionTemplates: SectionTemplate[] = [
  {
    id: 'blank',
    label: 'Blank',
    description: 'Single rich text block',
    content: {
      blocks: [
        {
          id: 'text-1',
          type: 'rich_text',
          props: { body: 'Page content goes here.' },
        },
      ],
    },
  },
  {
    id: 'landing',
    label: 'Landing',
    description: 'Hero, body copy, and call to action',
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
        {
          id: 'text-1',
          type: 'rich_text',
          props: {
            body: 'Edit this page in Studio, publish when ready, and share the public URL with your team.',
          },
        },
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
    id: 'contact-page',
    label: 'Contact page',
    description: 'Hero and contact form',
    content: {
      blocks: [
        {
          id: 'hero-1',
          type: 'hero',
          props: {
            headline: 'Contact us',
            subheadline: 'We would love to hear from you.',
          },
        },
        {
          id: 'contact-1',
          type: 'contact_form',
          props: {
            form_slug: 'contact',
            title: 'Send a message',
            submit_label: 'Send message',
          },
        },
      ],
    },
  },
  {
    id: 'full-landing',
    label: 'Full landing',
    description: 'Hero, content, CTA, and contact form',
    content: {
      blocks: [
        {
          id: 'hero-1',
          type: 'hero',
          props: {
            headline: 'Build your business site with Luma',
            subheadline: 'Structured content, pages, and navigation — without lock-in.',
          },
        },
        {
          id: 'text-1',
          type: 'rich_text',
          props: {
            body: 'Edit this page in Studio, publish when ready, and share the public URL with your team.',
          },
        },
        {
          id: 'cta-1',
          type: 'cta',
          props: {
            label: 'Open Studio',
            url: '/dashboard',
          },
        },
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
]

export function cloneSectionTemplateContent(template: SectionTemplate): PageContent {
  return JSON.parse(JSON.stringify(template.content)) as PageContent
}
