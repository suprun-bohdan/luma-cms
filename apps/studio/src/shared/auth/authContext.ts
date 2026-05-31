import { createContext } from 'react'
import type { AuthUser } from '../../features/auth/schemas/auth'
import type { AuthSession } from './authStorage'

export type AuthContextValue = {
  user: AuthUser | null
  token: string | null
  isAuthenticated: boolean
  loginSession: (session: AuthSession) => void
  logoutSession: () => void
  setUser: (user: AuthUser) => void
}

export const AuthContext = createContext<AuthContextValue | null>(null)
