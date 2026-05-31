import { describe, expect, it } from 'vitest'
import { resolveLocaleMessages } from './loadMessages'

describe('loadMessages', () => {
  it('resolves es_MX via es_ES overlay', () => {
    const messages = resolveLocaleMessages('es_MX')

    expect(messages['common.continue']).toBe('Continuar')
    expect(messages['requirements.php_version.label']).toBe('PHP version')
  })

  it('resolves uk with translated continue label', () => {
    const messages = resolveLocaleMessages('uk')

    expect(messages['common.continue']).toBe('Продовжити')
    expect(messages['welcome.start']).toBe('Почати налаштування')
  })

  it('includes all master keys for catalog locales', () => {
    const masterKeys = Object.keys(resolveLocaleMessages('en_US'))
    const ukKeys = Object.keys(resolveLocaleMessages('uk'))

    expect(ukKeys.sort()).toEqual(masterKeys.sort())
  })
})
