import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createPage,
  deletePage,
  getPage,
  listPages,
  publishPage,
  unpublishPage,
  updatePage,
} from '../api/pagesApi'
import type { PageStatus } from '../schemas/page'

export function usePages(status?: PageStatus) {
  return useQuery({
    queryKey: ['pages', status ?? 'all'],
    queryFn: () => listPages(status),
  })
}

export function usePage(slug: string | undefined) {
  return useQuery({
    queryKey: ['pages', 'detail', slug],
    queryFn: () => getPage(slug!),
    enabled: Boolean(slug),
  })
}

export function useCreatePage() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createPage,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['pages'] })
    },
  })
}

export function useUpdatePage(slug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: Record<string, unknown>) => updatePage(slug, body),
    onSuccess: (page) => {
      void queryClient.invalidateQueries({ queryKey: ['pages'] })
      void queryClient.invalidateQueries({ queryKey: ['pages', 'detail', page.slug] })
    },
  })
}

export function useDeletePage() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deletePage,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['pages'] })
    },
  })
}

export function usePublishPage() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: publishPage,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['pages'] })
    },
  })
}

export function useUnpublishPage() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: unpublishPage,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['pages'] })
    },
  })
}
