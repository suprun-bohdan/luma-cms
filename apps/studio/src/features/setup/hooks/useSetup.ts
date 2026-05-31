import { useMutation, useQuery } from '@tanstack/react-query'
import {
  fetchSetupLogs,
  fetchSetupRequirements,
  fetchSetupStatus,
  finishSetup,
  saveSetupDatabase,
  testSetupDatabase,
} from '../api/setupApi'

export function useSetupStatus() {
  return useQuery({
    queryKey: ['setup', 'status'],
    queryFn: fetchSetupStatus,
  })
}

export function useSetupRequirements(enabled: boolean) {
  return useQuery({
    queryKey: ['setup', 'requirements'],
    queryFn: fetchSetupRequirements,
    enabled,
  })
}

export function useSetupLogs(enabled: boolean) {
  return useQuery({
    queryKey: ['setup', 'logs'],
    queryFn: fetchSetupLogs,
    enabled,
    refetchInterval: enabled ? 3000 : false,
  })
}

export function useSetupDatabaseActions() {
  const testMutation = useMutation({
    mutationFn: testSetupDatabase,
  })

  const saveMutation = useMutation({
    mutationFn: saveSetupDatabase,
  })

  const finishMutation = useMutation({
    mutationFn: finishSetup,
  })

  return { testMutation, saveMutation, finishMutation }
}
