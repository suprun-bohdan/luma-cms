import type { LumaThemeMode } from './types'

export const THEME_STORAGE_KEY = 'luma-theme'

export function resolveThemeMode(stored: string | null): LumaThemeMode {
  if (stored === 'dark' || stored === 'light') {
    return stored
  }

  if (typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    return 'dark'
  }

  return 'light'
}

export function readStoredThemeMode(): LumaThemeMode {
  if (typeof window === 'undefined') {
    return 'light'
  }

  return resolveThemeMode(localStorage.getItem(THEME_STORAGE_KEY))
}

export function applyThemeMode(mode: LumaThemeMode): void {
  document.documentElement.setAttribute('data-luma-theme', mode)
  localStorage.setItem(THEME_STORAGE_KEY, mode)
}
