import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createField,
  deleteField,
  listFields,
  updateField,
} from '../api/fieldsApi'
import type { FieldFormValues } from '../schemas/field'

export function useFields(collectionSlug: string | undefined) {
  return useQuery({
    queryKey: ['fields', collectionSlug],
    queryFn: () => listFields(collectionSlug!),
    enabled: Boolean(collectionSlug),
  })
}

export function useCreateField(collectionSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: FieldFormValues) => createField(collectionSlug, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['fields', collectionSlug] })
      void queryClient.invalidateQueries({ queryKey: ['collections', collectionSlug] })
    },
  })
}

export function useUpdateField(collectionSlug: string, fieldSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: FieldFormValues) => updateField(collectionSlug, fieldSlug, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['fields', collectionSlug] })
      void queryClient.invalidateQueries({ queryKey: ['collections', collectionSlug] })
    },
  })
}

export function useDeleteField(collectionSlug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (fieldSlug: string) => deleteField(collectionSlug, fieldSlug),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['fields', collectionSlug] })
      void queryClient.invalidateQueries({ queryKey: ['collections', collectionSlug] })
    },
  })
}
