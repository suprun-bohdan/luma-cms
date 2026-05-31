#!/usr/bin/env node
/**
 * Generate primary setup wizard locale JSON files from en_US master + overlay seeds.
 */
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const localesDir = path.resolve(__dirname, '../src/features/setup/i18n/locales')
const masterPath = path.join(localesDir, 'en_US.json')
const seedsDir = path.resolve(__dirname, 'setup-locale-seeds')

const master = JSON.parse(fs.readFileSync(masterPath, 'utf8'))

/** Primary locales with dedicated wizard translations (regional variants use localeFallbacks.ts). */
const primaryLocales = [
  'uk',
  'ru_RU',
  'de_DE',
  'fr_FR',
  'es_ES',
  'pl_PL',
  'is_IS',
  'it_IT',
  'pt_BR',
  'pt_PT',
  'ja',
  'zh_CN',
  'zh_TW',
  'ar',
  'nl_NL',
  'sv_SE',
  'cs_CZ',
  'da_DK',
  'fi',
  'nb_NO',
  'ro_RO',
  'hu_HU',
  'tr_TR',
  'vi',
  'ko_KR',
  'fa_IR',
  'he_IL',
  'ca',
  'el',
  'hr',
  'sr_RS',
  'sk_SK',
  'bg_BG',
  'lt_LT',
  'lv',
  'et',
  'sl_SI',
  'bs_BA',
  'sq',
  'eu',
  'gl_ES',
  'cy',
  'mk_MK',
  'ka_GE',
  'hy',
  'uz_UZ',
  'ckb',
  'ur',
  'ne_NP',
  'eo',
  'id_ID',
  'th',
  'hi_IN',
  'bn_BD',
  'af',
  'sw',
  'kk',
  'bel',
]

function loadSeed(code) {
  const seedPath = path.join(seedsDir, `${code}.json`)
  if (!fs.existsSync(seedPath)) {
    return null
  }

  return JSON.parse(fs.readFileSync(seedPath, 'utf8'))
}

let written = 0

for (const code of primaryLocales) {
  const seed = loadSeed(code)
  const merged = seed ? { ...master, ...seed } : { ...master }
  const outPath = path.join(localesDir, `${code}.json`)
  fs.writeFileSync(outPath, `${JSON.stringify(merged, null, 2)}\n`)
  written += 1
}

console.log(`Generated ${written} locale files in ${localesDir}`)
