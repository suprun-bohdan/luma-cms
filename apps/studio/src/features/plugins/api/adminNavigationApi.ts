import { z } from 'zod'
import { apiGetList } from '../../../shared/api/client'

export const adminNavigationItemSchema = z.object({
  plugin_id: z.string(),
  label: z.string(),
  to: z.string(),
  sort_order: z.number(),
})

export type AdminNavigationItem = z.infer<typeof adminNavigationItemSchema>

export async function listAdminNavigationItems(): Promise<AdminNavigationItem[]> {
  return apiGetList('/api/v1/admin/navigation-items', adminNavigationItemSchema, { auth: true })
}
