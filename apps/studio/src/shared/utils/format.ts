import { ApiError } from '../api/client'
import type { AuthUser } from '../../features/auth/schemas/auth'

export function hasRole(user: AuthUser | null | undefined, role: string): boolean {
  return user?.roles.includes(role) ?? false
}

export function isOwner(user: AuthUser | null | undefined): boolean {
  return hasRole(user, 'owner')
}

export function permissionMessage(action: string, role = 'Owner'): string {
  return `${role} permission is required to ${action}.`
}

export function apiForbiddenMessage(error: unknown, fallback: string): string {
  if (error instanceof ApiError && error.status === 403) {
    return fallback
  }

  if (error instanceof Error) {
    return error.message
  }

  return fallback
}

export function canDeleteContent(user: AuthUser | null | undefined): boolean {
  return hasRole(user, 'admin')
}

export function canDeleteMedia(user: AuthUser | null | undefined): boolean {
  return hasRole(user, 'admin')
}

export function formatFileSize(bytes: number): string {
  if (bytes < 1024) {
    return `${bytes} B`
  }

  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} KB`
  }

  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

export function slugify(value: string): string {
  return value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

export function formatDate(value: string | null | undefined): string {
  if (!value) {
    return '—'
  }

  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

export function formatFieldErrors(
  errors?: Record<string, string[]>,
): string {
  if (!errors) {
    return 'Something went wrong.'
  }

  return Object.values(errors).flat().join(' ')
}
