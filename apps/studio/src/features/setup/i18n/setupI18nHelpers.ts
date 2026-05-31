import type { ApiTranslatableError, SetupRequirementCheck } from '../api/setupApi'

export function translateRequirementCheck(
  check: SetupRequirementCheck,
  t: (key: string, params?: Record<string, string | number>) => string,
): { label: string; message: string } {
  return {
    label: check.label_key ? t(check.label_key) : check.label,
    message: check.message_key
      ? t(check.message_key, check.message_params)
      : check.message,
  }
}

export function translateApiPayload(
  t: (key: string, params?: Record<string, string | number>) => string,
  payload: ApiTranslatableError | null | undefined,
  fallbackKey = 'errors.generic',
): string {
  if (payload?.message_key) {
    return t(payload.message_key, payload.message_params)
  }

  if (payload?.message) {
    return payload.message
  }

  return t(fallbackKey)
}

export function buildBlockedPasswordSet(): Set<string> {
  return new Set(['password', 'admin', 'change-me', 'password123'])
}
