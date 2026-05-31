import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createCollection,
  deleteCollection,
  getCollection,
  listCollections,
  updateCollection,
} from '../api/collectionsApi'
import type { CollectionFormValues } from '../schemas/collection'

export function useCollections() {
  return useQuery({
    queryKey: ['collections'],
    queryFn: listCollections,
  })
}

export function useCollection(slug: string | undefined) {
  return useQuery({
    queryKey: ['collections', slug],
    queryFn: () => getCollection(slug!),
    enabled: Boolean(slug),
  })
}

export function useCreateCollection() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createCollection,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['collections'] })
    },
  })
}

export function useUpdateCollection(slug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: CollectionFormValues) => updateCollection(slug, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['collections'] })
      void queryClient.invalidateQueries({ queryKey: ['collections', slug] })
    },
  })
}

export function useDeleteCollection() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deleteCollection,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['collections'] })
    },
  })
}
