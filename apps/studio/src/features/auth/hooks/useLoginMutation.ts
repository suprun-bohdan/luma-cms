import { useMutation } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { login } from '../api/authApi'
import type { LoginPayload } from '../schemas/auth'
import { useAuth } from '../../../shared/auth/useAuth'

export function useLoginMutation() {
  const { loginSession } = useAuth()
  const navigate = useNavigate()

  return useMutation({
    mutationFn: (payload: LoginPayload) => login(payload),
    onSuccess: (session) => {
      loginSession(session)
      navigate('/collections')
    },
  })
}
