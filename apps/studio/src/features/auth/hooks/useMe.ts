import { useQuery } from '@tanstack/react-query'
import { fetchMe } from '../api/authApi'
import { useAuth } from '../../../shared/auth/useAuth'

export function useMe() {
  const { token, setUser, isAuthenticated } = useAuth()

  return useQuery({
    queryKey: ['auth', 'me'],
    queryFn: async () => {
      if (!token) {
        throw new Error('Missing token')
      }

      const user = await fetchMe(token)
      setUser(user)
      return user
    },
    enabled: isAuthenticated && token !== null,
    retry: false,
  })
}
