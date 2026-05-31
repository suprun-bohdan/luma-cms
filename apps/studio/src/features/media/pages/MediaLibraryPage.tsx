import { useRef, useState } from 'react'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ConfirmDialog } from '../../../shared/components/ConfirmDialog'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { ApiError } from '../../../shared/api/client'
import { canDeleteMedia, formatDate, formatFieldErrors, formatFileSize } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import { getMediaPreviewUrl } from '../api/mediaApi'
import { useDeleteMedia, useMediaList, useUpdateMedia, useUploadMedia } from '../hooks/useMedia'
import type { Media } from '../schemas/media'

export function MediaLibraryPage() {
  const { user } = useAuth()
  const mediaQuery = useMediaList()
  const uploadMutation = useUploadMedia()
  const deleteMutation = useDeleteMedia()
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [altText, setAltText] = useState('')
  const [editingMedia, setEditingMedia] = useState<Media | null>(null)
  const [editAltText, setEditAltText] = useState('')
  const updateMutation = useUpdateMedia(editingMedia?.uuid ?? '')
  const [pendingDelete, setPendingDelete] = useState<Media | null>(null)
  const canDelete = canDeleteMedia(user)

  const uploadError =
    uploadMutation.error instanceof ApiError
      ? formatFieldErrors(uploadMutation.error.errors) || uploadMutation.error.message
      : uploadMutation.error instanceof Error
        ? uploadMutation.error.message
        : null

  return (
    <>
      <PageHeader
        title="Media"
        description="Upload and manage images and files for your pages."
        actions={
          <Button
            disabled={uploadMutation.isPending}
            onClick={() => fileInputRef.current?.click()}
          >
            {uploadMutation.isPending ? 'Uploading…' : 'Upload file'}
          </Button>
        }
      />

      <Card className="mb-6">
        <div className="flex flex-col gap-4 md:flex-row md:items-end">
          <Input
            label="Alt text for next upload (optional)"
            value={altText}
            onChange={(event) => setAltText(event.target.value)}
          />
          <input
            ref={fileInputRef}
            type="file"
            className="hidden"
            accept="image/jpeg,image/png,image/webp,image/gif,application/pdf"
            onChange={(event) => {
              const file = event.target.files?.[0]
              if (!file) {
                return
              }

              uploadMutation.mutate(
                { file, altText },
                {
                  onSuccess: () => {
                    setAltText('')
                    event.target.value = ''
                  },
                },
              )
            }}
          />
        </div>
        <HelpText className="mt-4">
          Accepted types: JPEG, PNG, WebP, GIF, and PDF. Maximum upload size depends on your server
          PHP settings (typically 2–10 MB). Add alt text so images are accessible on the public site.
        </HelpText>
        {uploadError && (
          <div className="mt-4">
            <ErrorAlert message={uploadError} />
          </div>
        )}
      </Card>

      {mediaQuery.isLoading && <LoadingState message="Loading media library…" />}
      {mediaQuery.isError && <ErrorAlert message={mediaQuery.error.message} />}

      {mediaQuery.data?.length === 0 && (
        <EmptyState
          title="No media yet"
          description="Upload images or PDFs to use in page blocks and SEO previews."
          action={
            <Button
              disabled={uploadMutation.isPending}
              onClick={() => fileInputRef.current?.click()}
            >
              Upload first file
            </Button>
          }
        />
      )}

      {mediaQuery.data && mediaQuery.data.length > 0 && (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          {mediaQuery.data.map((media) => (
            <Card key={media.uuid} className="overflow-hidden">
              <div className="mb-4 aspect-video overflow-hidden rounded-lg bg-slate-100">
                {media.mime_type.startsWith('image/') ? (
                  <img
                    src={getMediaPreviewUrl(media)}
                    alt={media.alt_text ?? media.filename}
                    className="h-full w-full object-cover"
                  />
                ) : (
                  <div className="flex h-full items-center justify-center text-sm text-slate-500">
                    {media.mime_type}
                  </div>
                )}
              </div>
              <div className="space-y-1">
                <p className="truncate font-medium text-slate-900">{media.filename}</p>
                <p className="text-xs text-slate-500">
                  {formatFileSize(media.size)} · {formatDate(media.created_at)}
                </p>
                <p className="text-sm text-slate-600">
                  Alt: {media.alt_text?.trim() ? media.alt_text : '—'}
                </p>
              </div>
              <div className="mt-4 flex flex-wrap gap-2">
                <Button
                  variant="secondary"
                  onClick={() => {
                    setEditingMedia(media)
                    setEditAltText(media.alt_text ?? '')
                  }}
                >
                  Edit alt
                </Button>
                <Button variant="ghost" onClick={() => void navigator.clipboard.writeText(media.url)}>
                  Copy URL
                </Button>
                {canDelete && (
                  <Button variant="danger" onClick={() => setPendingDelete(media)}>
                    Delete
                  </Button>
                )}
              </div>
            </Card>
          ))}
        </div>
      )}

      {editingMedia && (
        <div className="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4">
          <Card className="w-full max-w-md">
            <h2 className="mb-4 text-lg font-medium">Edit alt text</h2>
            <Input
              label="Alt text"
              value={editAltText}
              onChange={(event) => setEditAltText(event.target.value)}
            />
            <div className="mt-4 flex gap-2">
              <Button
                disabled={updateMutation.isPending}
                onClick={() =>
                  updateMutation.mutate(
                    { alt_text: editAltText.trim() ? editAltText.trim() : null },
                    { onSuccess: () => setEditingMedia(null) },
                  )
                }
              >
                Save
              </Button>
              <Button variant="ghost" onClick={() => setEditingMedia(null)}>
                Cancel
              </Button>
            </div>
          </Card>
        </div>
      )}

      <ConfirmDialog
        open={pendingDelete !== null}
        title="Delete media"
        description={
          pendingDelete
            ? `Permanently delete "${pendingDelete.filename}" from storage? Pages using this file may show broken images.`
            : 'This permanently removes the file from storage.'
        }
        confirmLabel="Delete"
        loading={deleteMutation.isPending}
        onCancel={() => setPendingDelete(null)}
        onConfirm={() => {
          if (!pendingDelete) {
            return
          }

          deleteMutation.mutate(pendingDelete.uuid, {
            onSuccess: () => setPendingDelete(null),
          })
        }}
      />
    </>
  )
}
