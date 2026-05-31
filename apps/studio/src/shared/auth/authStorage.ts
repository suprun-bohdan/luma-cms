export type StoredUser = {
  id: number
  name: string
  email: string
  roles: string[]
}

const TOKEN_KEY = 'luma.auth.token'
const USER_KEY = 'luma.auth.user'

export type AuthSession = {
  token: string
  user: StoredUser
}

export function getToken(): string | null {
  return sessionStorage.getItem(TOKEN_KEY)
}

export function getStoredUser(): StoredUser | null {
  const raw = sessionStorage.getItem(USER_KEY)
  if (!raw) {
    return null
  }

  return JSON.parse(raw) as StoredUser
}

export function setSession(session: AuthSession): void {
  sessionStorage.setItem(TOKEN_KEY, session.token)
  sessionStorage.setItem(USER_KEY, JSON.stringify(session.user))
}

export function clearSession(): void {
  sessionStorage.removeItem(TOKEN_KEY)
  sessionStorage.removeItem(USER_KEY)
}

export function hasSession(): boolean {
  return getToken() !== null
}
