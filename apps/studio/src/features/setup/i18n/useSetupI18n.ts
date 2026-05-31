import { useContext } from 'react'
import { SetupI18nContext } from './setupI18nContext'

export function useSetupI18n() {
  const context = useContext(SetupI18nContext)

  if (!context) {
    throw new Error('useSetupI18n must be used within SetupI18nProvider')
  }

  return context
}
