import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { deleteMedia, listMedia, updateMedia, uploadMedia } from '../api/mediaApi'
import type { UpdateMediaValues } from '../schemas/media'

export function useMediaList() {
  return useQuery({
    queryKey: ['media'],
    queryFn: listMedia,
  })
}

export function useUploadMedia() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ file, altText }: { file: File; altText?: string }) =>
      uploadMedia(file, altText),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['media'] })
    },
  })
}

export function useUpdateMedia(uuid: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (body: UpdateMediaValues) => updateMedia(uuid, body),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['media'] })
    },
  })
}

export function useDeleteMedia() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deleteMedia,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['media'] })
    },
  })
}
