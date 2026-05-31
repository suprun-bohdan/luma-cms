import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiGet, apiRequest } from '../../../shared/api/client'
import { z } from 'zod'

const settingsSchema = z.object({
  data: z.object({
    site: z.object({
      title: z.string(),
      tagline: z.string(),
      locale: z.string(),
      timezone: z.string(),
    }),
    seo: z.object({
      default_title_suffix: z.string(),
    }),
  }),
})

export function useSettings() {
  return useQuery({
    queryKey: ['settings'],
    queryFn: async () => {
      const response = await apiGet('/api/v1/settings', settingsSchema, { auth: true })
      return response.data
    },
  })
}

export function useUpdateSettings() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (payload: Record<string, string>) =>
      apiRequest({
        path: '/api/v1/settings',
        method: 'PATCH',
        body: payload,
        schema: settingsSchema,
        auth: true,
      }),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['settings'] })
    },
  })
}
