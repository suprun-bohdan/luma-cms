import { describe, expect, it } from 'vitest'
import { detectSuggestedLocale } from './detectLocale'
import { resolveLocaleChain } from './localeFallbacks'

describe('detectSuggestedLocale', () => {
  it('falls back to en_US when navigator is unavailable', () => {
    const original = globalThis.navigator
    Object.defineProperty(globalThis, 'navigator', {
      configurable: true,
      value: undefined,
    })

    expect(detectSuggestedLocale()).toEqual({ locale: 'en_US', regionHint: null })

    Object.defineProperty(globalThis, 'navigator', {
      configurable: true,
      value: original,
    })
  })
})

describe('resolveLocaleChain', () => {
  it('resolves es_MX via es_ES fallback', () => {
    expect(resolveLocaleChain('es_MX')).toEqual(['es_MX', 'es_ES', 'en_US'])
  })

  it('resolves uk without extra fallbacks before en_US', () => {
    expect(resolveLocaleChain('uk')).toEqual(['uk', 'en_US'])
  })
})
