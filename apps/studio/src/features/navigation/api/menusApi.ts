import { z } from 'zod'
import { apiGetList, apiRequest } from '../../../shared/api/client'
import { menuSchema, type Menu } from '../schemas/menu'

export async function listMenus(): Promise<Menu[]> {
  return apiGetList('/api/v1/menus', menuSchema, { auth: true })
}

export async function getMenu(slug: string): Promise<Menu> {
  return apiRequest({
    method: 'GET',
    path: `/api/v1/menus/${slug}`,
    schema: menuSchema,
    auth: true,
  })
}

export async function createMenu(body: Record<string, unknown>): Promise<Menu> {
  return apiRequest({
    method: 'POST',
    path: '/api/v1/menus',
    body,
    schema: menuSchema,
    auth: true,
  })
}

export async function updateMenu(slug: string, body: Record<string, unknown>): Promise<Menu> {
  return apiRequest({
    method: 'PUT',
    path: `/api/v1/menus/${slug}`,
    body,
    schema: menuSchema,
    auth: true,
  })
}

export async function deleteMenu(slug: string): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/menus/${slug}`,
    schema: z.null(),
    auth: true,
  })
}
