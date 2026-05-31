const STORAGE_KEY = 'luma-setup-wizard-v2'
const LEGACY_STORAGE_KEY = 'luma-setup-wizard-v1'

export type SetupWizardPersistedState = {
  stepIndex: number
  driver: string
  host: string
  port: string
  database: string
  username: string
  siteTitle: string
  ownerName: string
  adminEmail: string
  withStarterSite: boolean
  locale: string
  allowWeakPassword: boolean
}

const defaultState: SetupWizardPersistedState = {
  stepIndex: 0,
  driver: 'mysql',
  host: '127.0.0.1',
  port: '3306',
  database: '',
  username: '',
  siteTitle: '',
  ownerName: '',
  adminEmail: '',
  withStarterSite: true,
  locale: 'en_US',
  allowWeakPassword: false,
}

function normalizeState(parsed: Partial<SetupWizardPersistedState>): SetupWizardPersistedState | null {
  if (typeof parsed.stepIndex !== 'number' || parsed.stepIndex < 0 || parsed.stepIndex > 5) {
    return null
  }

  return {
    ...defaultState,
    ...parsed,
    locale: typeof parsed.locale === 'string' && parsed.locale !== '' ? parsed.locale : defaultState.locale,
    allowWeakPassword: parsed.allowWeakPassword === true,
  }
}

export function loadSetupWizardState(): Partial<SetupWizardPersistedState> | null {
  if (typeof window === 'undefined') {
    return null
  }

  const raw = window.localStorage.getItem(STORAGE_KEY) ?? window.localStorage.getItem(LEGACY_STORAGE_KEY)

  if (!raw) {
    return null
  }

  try {
    const parsed = JSON.parse(raw) as Partial<SetupWizardPersistedState>
    return normalizeState(parsed)
  } catch {
    return null
  }
}

export function saveSetupWizardState(state: SetupWizardPersistedState): void {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
  window.localStorage.removeItem(LEGACY_STORAGE_KEY)
}

export function clearSetupWizardState(): void {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.removeItem(STORAGE_KEY)
  window.localStorage.removeItem(LEGACY_STORAGE_KEY)
}
