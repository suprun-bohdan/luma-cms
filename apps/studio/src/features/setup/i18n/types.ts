export type SetupLocaleCode = string

export type SetupMessages = Record<string, string>

export type WordPressLanguageEntry = {
  code: SetupLocaleCode
  englishName: string
  nativeName: string
  iso: string
}

export type SetupI18nContextValue = {
  locale: SetupLocaleCode
  setLocale: (locale: SetupLocaleCode) => void
  t: (key: string, params?: Record<string, string | number>) => string
  dir: 'ltr' | 'rtl'
  ready: boolean
  suggestedLocale: SetupLocaleCode
  regionHint: string | null
  languages: readonly WordPressLanguageEntry[]
}

export type ApiTranslatableError = {
  message?: string
  message_key?: string
  message_params?: Record<string, string | number>
}
