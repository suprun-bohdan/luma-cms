import { useMe } from '../features/auth/hooks/useMe'
import { LoadingState } from '../shared/components/LoadingState'

type AuthBootstrapProps = {
  children: React.ReactNode
}

export function AuthBootstrap({ children }: AuthBootstrapProps) {
  const meQuery = useMe()

  if (meQuery.isLoading) {
    return <LoadingState message="Loading session…" />
  }

  return children
}
