#!/usr/bin/env node
/**
 * Validates that every WordPress catalog locale resolves to a complete wizard message set.
 */
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const i18nDir = path.resolve(__dirname, '../src/features/setup/i18n')
const localesDir = path.join(i18nDir, 'locales')

const master = JSON.parse(fs.readFileSync(path.join(localesDir, 'en_US.json'), 'utf8'))
const masterKeys = Object.keys(master)

const catalogSource = fs.readFileSync(path.join(i18nDir, 'languageCatalog.ts'), 'utf8')
const catalogCodes = [...catalogSource.matchAll(/code: '([^']+)'/g)].map((m) => m[1])

const fallbackSource = fs.readFileSync(path.join(i18nDir, 'localeFallbacks.ts'), 'utf8')
const fallbackMap = Object.fromEntries(
  [...fallbackSource.matchAll(/^\s+([a-zA-Z0-9_]+):\s+'([^']+)',/gm)].map((m) => [m[1], m[2]]),
)

const localeFiles = Object.fromEntries(
  fs
    .readdirSync(localesDir)
    .filter((name) => name.endsWith('.json'))
    .map((name) => [name.replace(/\.json$/, ''), JSON.parse(fs.readFileSync(path.join(localesDir, name), 'utf8'))]),
)

function resolveChain(locale) {
  const chain = []
  const seen = new Set()
  let current = locale

  while (current && !seen.has(current)) {
    seen.add(current)
    chain.push(current)
    current = fallbackMap[current]
  }

  if (!seen.has('en_US')) {
    chain.push('en_US')
  }

  return chain
}

function resolveMessages(locale) {
  let messages = { ...master }

  for (const code of resolveChain(locale)) {
    if (code === 'en_US') {
      continue
    }

    const overlay = localeFiles[code]
    if (overlay) {
      messages = { ...messages, ...overlay }
    }
  }

  return messages
}

let failures = 0

for (const code of catalogCodes) {
  const messages = resolveMessages(code)
  const missing = masterKeys.filter((key) => messages[key] === undefined || messages[key] === '')

  if (missing.length > 0) {
    failures += 1
    console.error(`[FAIL] ${code} missing ${missing.length} keys: ${missing.slice(0, 5).join(', ')}`)
  }
}

if (failures > 0) {
  console.error(`Validation failed for ${failures} locales.`)
  process.exit(1)
}

console.log(`Validated ${catalogCodes.length} catalog locales against ${masterKeys.length} wizard keys.`)
