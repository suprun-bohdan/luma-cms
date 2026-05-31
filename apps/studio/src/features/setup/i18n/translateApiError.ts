import { ApiError } from '../../../shared/api/client'
import type { ApiTranslatableError } from './types'

export function translateMessage(
  t: (key: string, params?: Record<string, string | number>) => string,
  payload: ApiTranslatableError | null | undefined,
  fallback = 'Something went wrong.',
): string {
  if (!payload) {
    return fallback
  }

  if (payload.message_key) {
    return t(payload.message_key, payload.message_params)
  }

  if (payload.message) {
    return payload.message
  }

  return fallback
}

export function translateApiError(
  t: (key: string, params?: Record<string, string | number>) => string,
  error: unknown,
  fallbackKey = 'errors.generic',
): string {
  if (error instanceof ApiError) {
    if (error.messageKey) {
      return t(error.messageKey, error.messageParams)
    }

    if (error.message) {
      return error.message
    }
  }

  if (error && typeof error === 'object' && 'messageKey' in error) {
    const keyed = error as { messageKey?: string; messageParams?: Record<string, string | number>; message?: string }

    if (keyed.messageKey) {
      return t(keyed.messageKey, keyed.messageParams)
    }

    if (keyed.message) {
      return keyed.message
    }
  }

  if (error instanceof Error && error.message) {
    return error.message
  }

  return t(fallbackKey)
}
