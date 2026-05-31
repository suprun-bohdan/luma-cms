import { z } from 'zod'

export const authUserSchema = z.object({
  id: z.number(),
  name: z.string(),
  email: z.string(),
  roles: z.array(z.string()),
})

export type AuthUser = z.infer<typeof authUserSchema>

export const loginPayloadSchema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
})

export type LoginPayload = z.infer<typeof loginPayloadSchema>

export const loginResponseSchema = z.object({
  data: z.object({
    token: z.string(),
    token_type: z.literal('Bearer'),
    user: authUserSchema,
  }),
})

export type LoginResponse = z.infer<typeof loginResponseSchema>
