import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { applyThemeMode, readStoredThemeMode } from './shared/theme/applyTheme'
import './styles/index.scss'
import './styles/tailwind.css'
import App from './app/App.tsx'

applyThemeMode(readStoredThemeMode())

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
