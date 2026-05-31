import { z } from 'zod'
import {
  apiRequest,
  wrappedSchema,
} from '../../../shared/api/client'
import {
  authUserSchema,
  loginPayloadSchema,
  loginResponseSchema,
  type AuthUser,
  type LoginPayload,
} from '../schemas/auth'

export async function login(payload: LoginPayload): Promise<{
  token: string
  user: AuthUser
}> {
  loginPayloadSchema.parse(payload)

  const response = await apiRequest({
    method: 'POST',
    path: '/api/v1/auth/login',
    body: payload,
    schema: loginResponseSchema,
  })

  return {
    token: response.data.token,
    user: response.data.user,
  }
}

export async function logout(token: string): Promise<void> {
  await apiRequest({
    method: 'POST',
    path: '/api/v1/auth/logout',
    schema: z.null(),
    token,
  })
}

export async function fetchMe(token: string): Promise<AuthUser> {
  const response = await apiRequest({
    method: 'GET',
    path: '/api/v1/auth/me',
    schema: wrappedSchema(authUserSchema),
    token,
  })

  return response.data
}
