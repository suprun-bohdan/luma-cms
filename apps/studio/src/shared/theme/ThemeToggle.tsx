import { Button } from '../components/Button'
import { useTheme } from './useTheme'

export function ThemeToggle() {
  const { mode, toggleMode } = useTheme()

  return (
    <Button
      type="button"
      variant="secondary"
      onClick={toggleMode}
      aria-label={mode === 'light' ? 'Switch to dark theme' : 'Switch to light theme'}
    >
      {mode === 'light' ? 'Dark theme' : 'Light theme'}
    </Button>
  )
}
