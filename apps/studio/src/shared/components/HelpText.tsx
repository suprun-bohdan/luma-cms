type HelpTextProps = {
  children: React.ReactNode
  className?: string
}

export function HelpText({ children, className = '' }: HelpTextProps) {
  return (
    <p className={`text-sm text-[var(--luma-color-text-muted)] ${className}`.trim()}>{children}</p>
  )
}
