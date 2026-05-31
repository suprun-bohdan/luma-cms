import { createContext } from 'react'
import type { SetupI18nContextValue } from './types'

export const SetupI18nContext = createContext<SetupI18nContextValue | null>(null)
