import type { z } from 'zod'

const apiBase = import.meta.env.VITE_API_BASE_URL ?? ''

export class ApiError extends Error {
  public readonly status: number

  constructor(message: string, status: number) {
    super(message)
    this.name = 'ApiError'
    this.status = status
  }
}

export async function apiGet<T>(
  path: string,
  schema: z.ZodType<T>,
): Promise<T> {
  const response = await fetch(`${apiBase}${path}`)

  if (!response.ok) {
    throw new ApiError(`Request failed: ${response.status}`, response.status)
  }

  const data: unknown = await response.json()
  return schema.parse(data)
}
