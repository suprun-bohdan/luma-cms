/** WordPress regional locale → base locale with full wizard translations. */
export const localeFallbackMap: Record<string, string> = {
  de_AT: 'de_DE',
  de_CH: 'de_DE',
  de_CH_informal: 'de_DE',
  de_DE_formal: 'de_DE',
  en_AU: 'en_US',
  en_CA: 'en_US',
  en_GB: 'en_US',
  en_NZ: 'en_US',
  en_ZA: 'en_US',
  es_AR: 'es_ES',
  es_CL: 'es_ES',
  es_CO: 'es_ES',
  es_CR: 'es_ES',
  es_DO: 'es_ES',
  es_EC: 'es_ES',
  es_GT: 'es_ES',
  es_MX: 'es_ES',
  es_PE: 'es_ES',
  es_PR: 'es_ES',
  es_UY: 'es_ES',
  es_VE: 'es_ES',
  fr_BE: 'fr_FR',
  fr_CA: 'fr_FR',
  nl_BE: 'nl_NL',
  nl_NL_formal: 'nl_NL',
  pt_AO: 'pt_PT',
  pt_PT_ao90: 'pt_PT',
  pt_BR: 'pt_BR',
  zh_HK: 'zh_TW',
  fa_AF: 'fa_IR',
  ary: 'ar',
  azb: 'az',
  dsb: 'de_DE',
  hsb: 'de_DE',
  jv_ID: 'id_ID',
  ms_MY: 'id_ID',
  ta_LK: 'ta_IN',
  szl: 'pl_PL',
  kir: 'ru_RU',
  tt_RU: 'ru_RU',
  bel: 'ru_RU',
}

const rtlLocales = new Set(['ar', 'ary', 'fa_IR', 'fa_AF', 'he_IL', 'ur', 'ps', 'ckb', 'ug_CN', 'azb', 'haz', 'skr', 'snd'])

export function resolveLocaleChain(locale: string): string[] {
  const chain: string[] = []
  const seen = new Set<string>()
  let current: string | undefined = locale

  while (current && !seen.has(current)) {
    seen.add(current)
    chain.push(current)
    current = localeFallbackMap[current]
  }

  if (!seen.has('en_US')) {
    chain.push('en_US')
  }

  return chain
}

export function localeDirection(locale: string): 'ltr' | 'rtl' {
  const base = locale.split('_')[0]

  if (rtlLocales.has(locale) || rtlLocales.has(base)) {
    return 'rtl'
  }

  return 'ltr'
}

export function normalizeLocaleCode(input: string): string {
  const trimmed = input.trim().replace(/-/g, '_')

  if (trimmed === 'en') {
    return 'en_US'
  }

  return trimmed
}
