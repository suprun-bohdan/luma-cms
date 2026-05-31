#!/usr/bin/env node
/**
 * Regenerate languageCatalog.ts from WordPress translations API (dev-only snapshot).
 * Usage: node scripts/build-setup-language-catalog.mjs [path-to-wp-json]
 */
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const defaultInput = '/tmp/wp-translations.json'
const inputPath = process.argv[2] ?? defaultInput
const outputPath = path.resolve(__dirname, '../src/features/setup/i18n/languageCatalog.ts')

if (!fs.existsSync(inputPath)) {
  console.error(`Missing input JSON: ${inputPath}`)
  console.error('Download: curl -s https://api.wordpress.org/translations/core/1.0/ -o /tmp/wp-translations.json')
  process.exit(1)
}

const wp = JSON.parse(fs.readFileSync(inputPath, 'utf8'))
const entries = wp.translations.map((t) => ({
  code: t.language,
  englishName: t.english_name,
  nativeName: t.native_name,
  iso: t.iso?.['1'] || t.language.split('_')[0],
}))

const out = `import type { WordPressLanguageEntry } from './types'

/** Snapshot from WordPress translations API — regenerate via scripts/build-setup-language-catalog.mjs */
export const wordPressLanguageCatalog: readonly WordPressLanguageEntry[] = [
  { code: 'en_US', englishName: 'English (United States)', nativeName: 'English (United States)', iso: 'en' },
${entries
  .map(
    (e) =>
      `  { code: '${e.code}', englishName: ${JSON.stringify(e.englishName)}, nativeName: ${JSON.stringify(e.nativeName)}, iso: '${e.iso}' },`,
  )
  .join('\n')}
] as const
`

fs.writeFileSync(outputPath, out)
console.log(`Wrote ${outputPath} (${entries.length} locales)`)
