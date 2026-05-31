import { useMemo, useState, type ReactNode } from 'react'
import type { AuthUser } from '../../features/auth/schemas/auth'
import {
  clearSession,
  getStoredUser,
  getToken,
  setSession,
  type AuthSession,
} from './authStorage'
import { AuthContext, type AuthContextValue } from './authContext'

type AuthProviderProps = {
  children: ReactNode
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [token, setToken] = useState<string | null>(() => getToken())
  const [user, setUserState] = useState<AuthUser | null>(() => getStoredUser())

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      token,
      isAuthenticated: token !== null,
      loginSession: (session: AuthSession) => {
        setSession(session)
        setToken(session.token)
        setUserState(session.user)
      },
      logoutSession: () => {
        clearSession()
        setToken(null)
        setUserState(null)
      },
      setUser: (nextUser: AuthUser) => {
        const currentToken = getToken()
        if (currentToken) {
          setSession({ token: currentToken, user: nextUser })
        }
        setUserState(nextUser)
      },
    }),
    [token, user],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
