import { Button } from '../components/Button'
import { useTheme } from './useTheme'

type ThemeToggleLabels = {
  lightLabel: string
  darkLabel: string
  lightAria: string
  darkAria: string
}

type ThemeToggleProps = {
  labels?: ThemeToggleLabels
}

export function ThemeToggle({ labels }: ThemeToggleProps) {
  const { mode, toggleMode } = useTheme()

  const lightLabel = labels?.lightLabel ?? 'Dark theme'
  const darkLabel = labels?.darkLabel ?? 'Light theme'
  const lightAria = labels?.lightAria ?? 'Switch to dark theme'
  const darkAria = labels?.darkAria ?? 'Switch to light theme'

  return (
    <Button
      type="button"
      variant="secondary"
      onClick={toggleMode}
      aria-label={mode === 'light' ? lightAria : darkAria}
    >
      {mode === 'light' ? lightLabel : darkLabel}
    </Button>
  )
}
