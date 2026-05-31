import { useQuery } from '@tanstack/react-query'
import { listAdminNavigationItems } from '../api/adminNavigationApi'

export function useAdminNavigation() {
  return useQuery({
    queryKey: ['admin-navigation'],
    queryFn: listAdminNavigationItems,
    retry: false,
  })
}
