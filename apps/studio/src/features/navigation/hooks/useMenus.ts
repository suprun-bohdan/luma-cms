import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createMenu, getMenu, listMenus, updateMenu } from '../api/menusApi'

export function useMenus() {
  return useQuery({
    queryKey: ['menus'],
    queryFn: listMenus,
  })
}

export function useMenu(slug: string | undefined) {
  return useQuery({
    queryKey: ['menus', slug],
    queryFn: () => getMenu(slug!),
    enabled: Boolean(slug),
    retry: false,
  })
}

export function useCreateMenu() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createMenu,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['menus'] })
    },
  })
}

export function useUpdateMenu(slug: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: Record<string, unknown>) => updateMenu(slug, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['menus'] })
      void queryClient.invalidateQueries({ queryKey: ['menus', slug] })
    },
  })
}
