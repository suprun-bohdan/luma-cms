import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createForm,
  deleteForm,
  getForm,
  listFormSubmissions,
  listForms,
  updateForm,
} from '../api/formsApi'

export function useForms() {
  return useQuery({
    queryKey: ['forms'],
    queryFn: listForms,
  })
}

export function useForm(slug: string | undefined) {
  return useQuery({
    queryKey: ['forms', slug],
    queryFn: () => getForm(slug!),
    enabled: slug !== undefined,
  })
}

export function useFormSubmissions(slug: string | undefined) {
  return useQuery({
    queryKey: ['forms', slug, 'submissions'],
    queryFn: () => listFormSubmissions(slug!),
    enabled: slug !== undefined,
  })
}

export function useCreateForm() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createForm,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['forms'] })
    },
  })
}

export function useUpdateForm(slug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: Record<string, unknown>) => updateForm(slug, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['forms'] })
      void queryClient.invalidateQueries({ queryKey: ['forms', slug] })
    },
  })
}

export function useDeleteForm() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deleteForm,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['forms'] })
    },
  })
}
