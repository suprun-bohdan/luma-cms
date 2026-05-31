import { Navigate, Outlet } from 'react-router-dom'
import { LoadingState } from '../shared/components/LoadingState'
import { useOnboardingProgress } from '../features/onboarding/hooks/useOnboarding'

export function OnboardingGate() {
  const progressQuery = useOnboardingProgress()

  if (progressQuery.isLoading) {
    return <LoadingState message="Checking onboarding status…" />
  }

  if (progressQuery.data && !progressQuery.data.completed) {
    return <Navigate to="/onboarding" replace />
  }

  return <Outlet />
}
