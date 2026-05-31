import { createContext } from 'react'
import type { LumaThemeMode } from './types'

export type ThemeContextValue = {
  mode: LumaThemeMode
  setMode: (mode: LumaThemeMode) => void
  toggleMode: () => void
}

export const ThemeContext = createContext<ThemeContextValue | null>(null)
