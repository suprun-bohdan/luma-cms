import {
  createContext,
  useCallback,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import { detectSuggestedLocale } from './detectLocale'
import { wordPressLanguageCatalog } from './languageCatalog'
import { localeDirection } from './localeFallbacks'
import { loadMessages } from './loadMessages'
import type { SetupI18nContextValue, SetupMessages } from './types'
import { setSetupApiLocale } from '../api/setupApi'

export const SetupI18nContext = createContext<SetupI18nContextValue | null>(null)

type SetupI18nProviderProps = {
  children: ReactNode
  initialLocale?: string
  onLocaleChange?: (locale: string) => void
}

function interpolate(template: string, params?: Record<string, string | number>): string {
  if (!params) {
    return template
  }

  return template.replace(/\{(\w+)\}/g, (_, key: string) => {
    const value = params[key]
    return value === undefined ? `{${key}}` : String(value)
  })
}

export function SetupI18nProvider({
  children,
  initialLocale,
  onLocaleChange,
}: SetupI18nProviderProps) {
  const suggestion = useMemo(() => detectSuggestedLocale(), [])
  const [locale, setLocaleState] = useState(initialLocale ?? suggestion.locale)
  const [messages, setMessages] = useState<SetupMessages>(() => loadMessages(locale))

  const setLocale = useCallback(
    (nextLocale: string) => {
      setLocaleState(nextLocale)
      setMessages(loadMessages(nextLocale))
      onLocaleChange?.(nextLocale)
    },
    [onLocaleChange],
  )

  useEffect(() => {
    setSetupApiLocale(locale)
    document.documentElement.lang = locale.replace('_', '-')
    document.documentElement.dir = localeDirection(locale)
  }, [locale])

  const t = useCallback(
    (key: string, params?: Record<string, string | number>) => {
      const template = messages[key] ?? loadMessages('en_US')[key] ?? key
      return interpolate(template, params)
    },
    [messages],
  )

  const value = useMemo<SetupI18nContextValue>(
    () => ({
      locale,
      setLocale,
      t,
      dir: localeDirection(locale),
      ready: true,
      suggestedLocale: suggestion.locale,
      regionHint: suggestion.regionHint,
      languages: wordPressLanguageCatalog,
    }),
    [locale, setLocale, suggestion.locale, suggestion.regionHint, t],
  )

  return <SetupI18nContext.Provider value={value}>{children}</SetupI18nContext.Provider>
}
