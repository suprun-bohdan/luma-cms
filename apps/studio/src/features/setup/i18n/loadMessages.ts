import type { SetupMessages } from './types'
import { resolveLocaleChain } from './localeFallbacks'

import enUs from './locales/en_US.json'

const localeModules = import.meta.glob<SetupMessages>('./locales/*.json', {
  eager: true,
  import: 'default',
})

const localeCache = new Map<string, SetupMessages>()

function readLocaleFile(code: string): SetupMessages | null {
  return localeModules[`./locales/${code}.json`] ?? null
}

export function resolveLocaleMessages(locale: string): SetupMessages {
  const cached = localeCache.get(locale)
  if (cached) {
    return cached
  }

  const chain = resolveLocaleChain(locale)
  let messages: SetupMessages = { ...enUs }

  for (const code of chain) {
    if (code === 'en_US') {
      continue
    }

    const overlay = readLocaleFile(code)
    if (overlay) {
      messages = { ...messages, ...overlay }
    }
  }

  localeCache.set(locale, messages)

  return messages
}

export function loadMessages(locale: string): SetupMessages {
  return resolveLocaleMessages(locale)
}

export function getMasterMessageKeys(): string[] {
  return Object.keys(enUs)
}
