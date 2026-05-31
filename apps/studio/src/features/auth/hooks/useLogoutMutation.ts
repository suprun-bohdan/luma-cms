import { useMutation } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { logout } from '../api/authApi'
import { useAuth } from '../../../shared/auth/useAuth'

export function useLogoutMutation() {
  const { token, logoutSession } = useAuth()
  const navigate = useNavigate()

  return useMutation({
    mutationFn: async () => {
      if (!token) {
        return
      }

      await logout(token)
    },
    onSettled: () => {
      logoutSession()
      navigate('/login')
    },
  })
}
