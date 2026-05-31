import { wordPressLanguageCatalog } from './languageCatalog'
import { localeFallbackMap, normalizeLocaleCode } from './localeFallbacks'

const catalogCodes = new Set(wordPressLanguageCatalog.map((entry) => entry.code))

const regionToLocale: Record<string, string> = {
  UA: 'uk',
  IS: 'is_IS',
  RU: 'ru_RU',
  US: 'en_US',
  GB: 'en_GB',
  DE: 'de_DE',
  FR: 'fr_FR',
  ES: 'es_ES',
  PL: 'pl_PL',
  IT: 'it_IT',
  BR: 'pt_BR',
  PT: 'pt_PT',
  JP: 'ja',
  CN: 'zh_CN',
  TW: 'zh_TW',
  HK: 'zh_HK',
  KR: 'ko_KR',
  NL: 'nl_NL',
  BE: 'nl_BE',
  SE: 'sv_SE',
  NO: 'nb_NO',
  DK: 'da_DK',
  FI: 'fi',
  CZ: 'cs_CZ',
  SK: 'sk_SK',
  HU: 'hu_HU',
  RO: 'ro_RO',
  BG: 'bg_BG',
  HR: 'hr',
  RS: 'sr_RS',
  SI: 'sl_SI',
  LT: 'lt_LT',
  LV: 'lv',
  EE: 'et',
  GR: 'el',
  TR: 'tr_TR',
  IL: 'he_IL',
  SA: 'ar',
  AE: 'ar',
  IR: 'fa_IR',
  IN: 'hi_IN',
  ID: 'id_ID',
  VN: 'vi',
  TH: 'th',
  AU: 'en_AU',
  CA: 'en_CA',
  MX: 'es_MX',
  AR: 'es_AR',
  CO: 'es_CO',
  CL: 'es_CL',
  AT: 'de_AT',
  CH: 'de_CH',
}

const languageToLocale: Record<string, string> = {
  uk: 'uk',
  ru: 'ru_RU',
  is: 'is_IS',
  de: 'de_DE',
  fr: 'fr_FR',
  es: 'es_ES',
  pl: 'pl_PL',
  it: 'it_IT',
  pt: 'pt_PT',
  ja: 'ja',
  zh: 'zh_CN',
  ko: 'ko_KR',
  nl: 'nl_NL',
  sv: 'sv_SE',
  nb: 'nb_NO',
  nn: 'nn_NO',
  da: 'da_DK',
  fi: 'fi',
  cs: 'cs_CZ',
  sk: 'sk_SK',
  hu: 'hu_HU',
  ro: 'ro_RO',
  bg: 'bg_BG',
  hr: 'hr',
  sr: 'sr_RS',
  sl: 'sl_SI',
  lt: 'lt_LT',
  lv: 'lv',
  et: 'et',
  el: 'el',
  tr: 'tr_TR',
  he: 'he_IL',
  ar: 'ar',
  fa: 'fa_IR',
  hi: 'hi_IN',
  id: 'id_ID',
  vi: 'vi',
  th: 'th',
  en: 'en_US',
  ca: 'ca',
  eu: 'eu',
  gl: 'gl_ES',
  cy: 'cy',
  sq: 'sq',
  af: 'af',
  sw: 'sw',
  bn: 'bn_BD',
  ta: 'ta_IN',
  te: 'te',
  kn: 'kn',
  ml: 'ml_IN',
  ne: 'ne_NP',
  ur: 'ur',
  uz: 'uz_UZ',
  kk: 'kk',
  hy: 'hy',
  ka: 'ka_GE',
  mk: 'mk_MK',
  bs: 'bs_BA',
  eo: 'eo',
}

function pickCatalogLocale(candidate: string | undefined): string | null {
  if (!candidate) {
    return null
  }

  const normalized = normalizeLocaleCode(candidate)

  if (catalogCodes.has(normalized)) {
    return normalized
  }

  const mapped = localeFallbackMap[normalized]
  if (mapped && catalogCodes.has(mapped)) {
    return mapped
  }

  const language = normalized.split('_')[0]
  const fromLanguage = languageToLocale[language]

  if (fromLanguage && catalogCodes.has(fromLanguage)) {
    return fromLanguage
  }

  if (catalogCodes.has(language)) {
    return language
  }

  return null
}

export function detectSuggestedLocale(): { locale: string; regionHint: string | null } {
  if (typeof navigator === 'undefined') {
    return { locale: 'en_US', regionHint: null }
  }

  for (const tag of navigator.languages ?? [navigator.language]) {
    const match = pickCatalogLocale(tag)
    if (match) {
      return {
        locale: match,
        regionHint: extractRegionHint(tag),
      }
    }
  }

  const timezoneRegion = detectRegionFromTimezone()
  if (timezoneRegion) {
    const fromRegion = regionToLocale[timezoneRegion]
    const match = pickCatalogLocale(fromRegion)
    if (match) {
      return { locale: match, regionHint: timezoneRegion }
    }
  }

  return { locale: 'en_US', regionHint: null }
}

function extractRegionHint(tag: string): string | null {
  try {
    const locale = new Intl.Locale(tag)
    return locale.region ?? null
  } catch {
    const parts = tag.split(/[-_]/)
    return parts.length > 1 ? parts[1]?.toUpperCase() ?? null : null
  }
}

function detectRegionFromTimezone(): string | null {
  try {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
    if (!timezone) {
      return null
    }

    const map: Record<string, string> = {
      'Europe/Kyiv': 'UA',
      'Europe/Kiev': 'UA',
      'Atlantic/Reykjavik': 'IS',
      'Europe/Moscow': 'RU',
      'Europe/Berlin': 'DE',
      'Europe/Paris': 'FR',
      'Europe/Madrid': 'ES',
      'Europe/Warsaw': 'PL',
      'Europe/Rome': 'IT',
      'America/Sao_Paulo': 'BR',
      'Asia/Tokyo': 'JP',
      'Asia/Shanghai': 'CN',
      'Asia/Seoul': 'KR',
    }

    return map[timezone] ?? null
  } catch {
    return null
  }
}
