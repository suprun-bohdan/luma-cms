const STORAGE_KEY = 'luma-setup-wizard-v1'

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
}

export function loadSetupWizardState(): Partial<SetupWizardPersistedState> | null {
  if (typeof window === 'undefined') {
    return null
  }

  const raw = window.localStorage.getItem(STORAGE_KEY)

  if (!raw) {
    return null
  }

  try {
    const parsed = JSON.parse(raw) as Partial<SetupWizardPersistedState>

    if (typeof parsed.stepIndex !== 'number' || parsed.stepIndex < 0 || parsed.stepIndex > 5) {
      return null
    }

    return parsed
  } catch {
    return null
  }
}

export function saveSetupWizardState(state: SetupWizardPersistedState): void {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
}

export function clearSetupWizardState(): void {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.removeItem(STORAGE_KEY)
}
