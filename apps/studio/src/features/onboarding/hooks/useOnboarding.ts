import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  fetchOnboardingJournal,
  fetchOnboardingProgress,
  finishOnboarding,
  skipOnboardingIntegrations,
  submitOnboardingSiteType,
  submitOnboardingStarter,
  submitOnboardingWelcome,
} from '../api/onboardingApi'

export function useOnboardingProgress() {
  return useQuery({
    queryKey: ['onboarding', 'progress'],
    queryFn: fetchOnboardingProgress,
  })
}

export function useOnboardingJournal(enabled = true) {
  return useQuery({
    queryKey: ['onboarding', 'journal'],
    queryFn: fetchOnboardingJournal,
    enabled,
  })
}

export function useOnboardingActions() {
  const queryClient = useQueryClient()

  const invalidate = () => {
    void queryClient.invalidateQueries({ queryKey: ['onboarding'] })
    void queryClient.invalidateQueries({ queryKey: ['settings'] })
  }

  const welcomeMutation = useMutation({
    mutationFn: submitOnboardingWelcome,
    onSuccess: invalidate,
  })

  const siteTypeMutation = useMutation({
    mutationFn: submitOnboardingSiteType,
    onSuccess: invalidate,
  })

  const starterMutation = useMutation({
    mutationFn: submitOnboardingStarter,
    onSuccess: invalidate,
  })

  const integrationsMutation = useMutation({
    mutationFn: skipOnboardingIntegrations,
    onSuccess: invalidate,
  })

  const finishMutation = useMutation({
    mutationFn: finishOnboarding,
    onSuccess: invalidate,
  })

  return {
    welcomeMutation,
    siteTypeMutation,
    starterMutation,
    integrationsMutation,
    finishMutation,
  }
}
