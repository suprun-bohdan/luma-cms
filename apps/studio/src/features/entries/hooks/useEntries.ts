import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createEntry,
  deleteEntry,
  getEntry,
  listEntries,
  publishEntry,
  unpublishEntry,
  updateEntry,
} from '../api/entriesApi'
import type { EntryFormValues, EntryStatus } from '../schemas/entry'

export function useEntries(collectionSlug: string | undefined, status?: EntryStatus) {
  return useQuery({
    queryKey: ['entries', collectionSlug, status ?? 'all'],
    queryFn: () => listEntries(collectionSlug!, status),
    enabled: Boolean(collectionSlug),
  })
}

export function useEntry(id: number | undefined) {
  return useQuery({
    queryKey: ['entries', 'detail', id],
    queryFn: () => getEntry(id!),
    enabled: id !== undefined,
  })
}

export function useCreateEntry(collectionSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: EntryFormValues) => createEntry(collectionSlug, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['entries', collectionSlug] })
    },
  })
}

export function useUpdateEntry(id: number, collectionSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: EntryFormValues) => updateEntry(id, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['entries', collectionSlug] })
      void queryClient.invalidateQueries({ queryKey: ['entries', 'detail', id] })
    },
  })
}

export function useDeleteEntry(collectionSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deleteEntry,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['entries', collectionSlug] })
    },
  })
}

export function usePublishEntry(collectionSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: publishEntry,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['entries', collectionSlug] })
    },
  })
}

export function useUnpublishEntry(collectionSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: unpublishEntry,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['entries', collectionSlug] })
    },
  })
}
