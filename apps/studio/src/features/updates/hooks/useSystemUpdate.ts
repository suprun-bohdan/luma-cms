import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { fetchSystemVersion, fetchUpdateCheck, runSystemUpdate } from '../api/systemApi'

export function useSystemVersion() {
  return useQuery({
    queryKey: ['system', 'version'],
    queryFn: fetchSystemVersion,
  })
}

export function useUpdateCheck() {
  return useQuery({
    queryKey: ['system', 'update-check'],
    queryFn: fetchUpdateCheck,
  })
}

export function useRunSystemUpdate() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: runSystemUpdate,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['system'] })
      void queryClient.invalidateQueries({ queryKey: ['onboarding', 'journal'] })
    },
  })
}
