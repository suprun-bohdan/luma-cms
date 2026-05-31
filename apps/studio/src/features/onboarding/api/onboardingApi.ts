import { z } from 'zod'
import { apiGet, apiRequest } from '../../../shared/api/client'

const onboardingStepSchema = z.object({
  id: z.string(),
  label: z.string(),
  status: z.string(),
  completed_at: z.string().nullable(),
})

const onboardingProgressSchema = z.object({
  data: z.object({
    completed: z.boolean(),
    percent: z.number(),
    steps: z.array(onboardingStepSchema),
  }),
})

const onboardingJournalSchema = z.object({
  data: z.array(
    z.object({
      id: z.number(),
      step: z.string(),
      status: z.string(),
      message: z.string(),
      created_at: z.string().optional(),
    }),
  ),
})

export type OnboardingProgress = z.infer<typeof onboardingProgressSchema>['data']

export async function fetchOnboardingProgress() {
  const response = await apiGet('/api/v1/onboarding/progress', onboardingProgressSchema, { auth: true })
  return response.data
}

export async function fetchOnboardingJournal() {
  const response = await apiGet('/api/v1/onboarding/journal', onboardingJournalSchema, { auth: true })
  return response.data
}

export async function submitOnboardingWelcome(payload: { site_title: string; industry: string }) {
  return apiRequest({
    path: '/api/v1/onboarding/welcome',
    method: 'POST',
    body: payload,
    schema: onboardingProgressSchema,
    auth: true,
  })
}

export async function submitOnboardingSiteType(preset: 'business' | 'blog' | 'portfolio') {
  return apiRequest({
    path: '/api/v1/onboarding/site-type',
    method: 'POST',
    body: { preset },
    schema: onboardingProgressSchema,
    auth: true,
  })
}

export async function submitOnboardingStarter() {
  return apiRequest({
    path: '/api/v1/onboarding/starter',
    method: 'POST',
    body: {},
    schema: onboardingProgressSchema,
    auth: true,
  })
}

export async function skipOnboardingIntegrations() {
  return apiRequest({
    path: '/api/v1/onboarding/integrations',
    method: 'POST',
    body: {},
    schema: onboardingProgressSchema,
    auth: true,
  })
}

export async function finishOnboarding() {
  return apiRequest({
    path: '/api/v1/onboarding/finish',
    method: 'POST',
    body: {},
    schema: onboardingProgressSchema,
    auth: true,
  })
}
