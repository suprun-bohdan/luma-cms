import { z } from 'zod'

export const redirectSchema = z.object({
  id: z.number(),
  from_path: z.string(),
  to_path: z.string().nullable(),
  to_url: z.string().nullable(),
  status_code: z.number(),
  is_active: z.boolean(),
  created_at: z.string().nullable(),
  updated_at: z.string().nullable(),
})

export type Redirect = z.infer<typeof redirectSchema>

export const redirectFormSchema = z
  .object({
    from_path: z.string().min(1, 'From path is required'),
    to_path: z.string().optional(),
    to_url: z.string().url('Enter a valid URL').optional().or(z.literal('')),
    status_code: z.union([z.literal(301), z.literal(302)]),
    is_active: z.boolean(),
  })
  .superRefine((values, context) => {
    const hasPath = Boolean(values.to_path?.trim())
    const hasUrl = Boolean(values.to_url?.trim())

    if (!hasPath && !hasUrl) {
      context.addIssue({
        code: 'custom',
        message: 'Provide either an internal path or external URL.',
        path: ['to_path'],
      })
    }

    if (hasPath && hasUrl) {
      context.addIssue({
        code: 'custom',
        message: 'Use either internal path or external URL, not both.',
        path: ['to_url'],
      })
    }
  })

export type RedirectFormValues = z.infer<typeof redirectFormSchema>

export function redirectToFormValues(redirect: Redirect): RedirectFormValues {
  return {
    from_path: redirect.from_path,
    to_path: redirect.to_path ?? '',
    to_url: redirect.to_url ?? '',
    status_code: redirect.status_code === 302 ? 302 : 301,
    is_active: redirect.is_active,
  }
}

export function redirectFormToApiBody(values: RedirectFormValues): Record<string, unknown> {
  return {
    from_path: values.from_path.trim(),
    to_path: values.to_path?.trim() || null,
    to_url: values.to_url?.trim() || null,
    status_code: values.status_code,
    is_active: values.is_active,
  }
}
