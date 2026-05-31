import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createRedirect,
  deleteRedirect,
  getRedirect,
  listRedirects,
  updateRedirect,
} from '../api/redirectsApi'

export function useRedirects() {
  return useQuery({
    queryKey: ['redirects'],
    queryFn: listRedirects,
  })
}

export function useRedirect(id: number | undefined) {
  return useQuery({
    queryKey: ['redirects', id],
    queryFn: () => getRedirect(id!),
    enabled: id !== undefined,
  })
}

export function useCreateRedirect() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createRedirect,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['redirects'] })
    },
  })
}

export function useUpdateRedirect(id: number) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: Record<string, unknown>) => updateRedirect(id, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['redirects'] })
      void queryClient.invalidateQueries({ queryKey: ['redirects', id] })
    },
  })
}

export function useDeleteRedirect() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deleteRedirect,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['redirects'] })
    },
  })
}
