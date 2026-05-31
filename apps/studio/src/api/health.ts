import { apiGet } from './client'
import { healthResponseSchema, type HealthResponse } from '../schemas/health'

export function fetchHealth(): Promise<HealthResponse> {
  return apiGet('/api/v1/health', healthResponseSchema)
}
