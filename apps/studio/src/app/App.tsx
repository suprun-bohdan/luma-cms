import { QueryClientProvider } from '@tanstack/react-query'
import { RouterProvider } from 'react-router-dom'
import { AuthProvider } from '../shared/auth/AuthProvider'
import { ThemeProvider } from '../shared/theme/ThemeProvider'
import { queryClient } from './queryClient'
import { router } from './router'

export function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider>
        <AuthProvider>
          <RouterProvider router={router} />
        </AuthProvider>
      </ThemeProvider>
    </QueryClientProvider>
  )
}

export default App
