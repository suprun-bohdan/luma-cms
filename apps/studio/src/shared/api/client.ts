import { z } from 'zod'
import { getToken } from '../auth/authStorage'

const apiBase = import.meta.env.VITE_API_BASE_URL ?? ''

export class ApiError extends Error {
  public readonly status: number
  public readonly errors?: Record<string, string[]>
  public readonly messageKey?: string
  public readonly messageParams?: Record<string, string | number>

  constructor(
    message: string,
    status: number,
    errors?: Record<string, string[]>,
    messageKey?: string,
    messageParams?: Record<string, string | number>,
  ) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
    this.messageKey = messageKey
    this.messageParams = messageParams
  }
}

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

type ApiRequestOptions<T> = {
  method?: HttpMethod
  body?: unknown
  schema: z.ZodType<T>
  token?: string | null
  auth?: boolean
  headers?: Record<string, string>
}

async function parseJson(response: Response): Promise<unknown> {
  const text = await response.text()
  if (text === '') {
    return null
  }

  return JSON.parse(text) as unknown
}

function throwApiError(payload: unknown, status: number): never {
  const messageKey =
    typeof payload === 'object' &&
    payload !== null &&
    'message_key' in payload &&
    typeof payload.message_key === 'string'
      ? payload.message_key
      : undefined

  const messageParams =
    typeof payload === 'object' &&
    payload !== null &&
    'message_params' in payload &&
    typeof payload.message_params === 'object' &&
    payload.message_params !== null
      ? (payload.message_params as Record<string, string | number>)
      : undefined

  const message =
    typeof payload === 'object' &&
    payload !== null &&
    'message' in payload &&
    typeof payload.message === 'string'
      ? payload.message
      : `Request failed: ${status}`

  const errors =
    typeof payload === 'object' &&
    payload !== null &&
    'errors' in payload &&
    typeof payload.errors === 'object'
      ? (payload.errors as Record<string, string[]>)
      : undefined

  throw new ApiError(message, status, errors, messageKey, messageParams)
}

export async function apiRequest<T>({
  method = 'GET',
  path,
  body,
  schema,
  token,
  auth = false,
  headers: extraHeaders,
}: ApiRequestOptions<T> & { path: string }): Promise<T> {
  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...extraHeaders,
  }

  if (body !== undefined) {
    headers['Content-Type'] = 'application/json'
  }

  const bearer = token ?? (auth ? getToken() : null)
  if (bearer) {
    headers.Authorization = `Bearer ${bearer}`
  }

  const response = await fetch(`${apiBase}${path}`, {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
  })

  const payload = await parseJson(response)

  if (!response.ok) {
    throwApiError(payload, response.status)
  }

  if (response.status === 204) {
    return schema.parse(null)
  }

  return schema.parse(payload)
}

export function wrappedSchema<T extends z.ZodType>(itemSchema: T) {
  return z.object({ data: itemSchema })
}

export function wrappedListSchema<T extends z.ZodType>(itemSchema: T) {
  return z.object({ data: z.array(itemSchema) })
}

export async function apiGet<T>(
  path: string,
  schema: z.ZodType<T>,
  options?: { auth?: boolean; headers?: Record<string, string> },
): Promise<T> {
  return apiRequest({ method: 'GET', path, schema, auth: options?.auth, headers: options?.headers })
}

export async function apiGetWrapped<T>(
  path: string,
  itemSchema: z.ZodType<T>,
  options?: { auth?: boolean },
): Promise<T> {
  const response = await apiRequest({
    method: 'GET',
    path,
    schema: wrappedSchema(itemSchema),
    auth: options?.auth,
  })

  return response.data
}

export async function apiUpload<T>(
  path: string,
  formData: FormData,
  itemSchema: z.ZodType<T>,
  options?: { auth?: boolean },
): Promise<T> {
  const headers: Record<string, string> = {
    Accept: 'application/json',
  }

  const bearer = options?.auth ? getToken() : null
  if (bearer) {
    headers.Authorization = `Bearer ${bearer}`
  }

  const response = await fetch(`${apiBase}${path}`, {
    method: 'POST',
    headers,
    body: formData,
  })

  const payload = await parseJson(response)

  if (!response.ok) {
    throwApiError(payload, response.status)
  }

  const parsed = wrappedSchema(itemSchema).parse(payload)

  return parsed.data
}

export async function apiGetList<T>(
  path: string,
  itemSchema: z.ZodType<T>,
  options?: { auth?: boolean },
): Promise<T[]> {
  const response = await apiRequest({
    method: 'GET',
    path,
    schema: wrappedListSchema(itemSchema),
    auth: options?.auth,
  })

  return response.data
}
