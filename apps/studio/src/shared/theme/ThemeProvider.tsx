import { createContext, useCallback, useEffect, useMemo, useState, type ReactNode } from 'react'
import { applyThemeMode, readStoredThemeMode } from './applyTheme'
import type { LumaThemeMode } from './types'

type ThemeContextValue = {
  mode: LumaThemeMode
  setMode: (mode: LumaThemeMode) => void
  toggleMode: () => void
}

export const ThemeContext = createContext<ThemeContextValue | null>(null)

type ThemeProviderProps = {
  children: ReactNode
}

export function ThemeProvider({ children }: ThemeProviderProps) {
  const [mode, setModeState] = useState<LumaThemeMode>(() => readStoredThemeMode())

  useEffect(() => {
    applyThemeMode(mode)
  }, [mode])

  const setMode = useCallback((next: LumaThemeMode) => {
    setModeState(next)
  }, [])

  const toggleMode = useCallback(() => {
    setModeState((current) => (current === 'light' ? 'dark' : 'light'))
  }, [])

  const value = useMemo(
    () => ({
      mode,
      setMode,
      toggleMode,
    }),
    [mode, setMode, toggleMode],
  )

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
}
