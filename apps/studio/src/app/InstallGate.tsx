import { Navigate, Outlet } from 'react-router-dom'
import { LoadingState } from '../shared/components/LoadingState'
import { useSetupStatus } from '../features/setup/hooks/useSetup'

type InstallGateProps = {
  children?: React.ReactNode
}

export function InstallGate({ children }: InstallGateProps) {
  const statusQuery = useSetupStatus()

  if (statusQuery.isLoading) {
    return <LoadingState message="Checking installation status…" />
  }

  if (!statusQuery.data?.installed) {
    return <Navigate to="/setup" replace />
  }

  return children ? <>{children}</> : <Outlet />
}
