type HelpTextProps = {
  children: React.ReactNode
  className?: string
}

export function HelpText({ children, className = '' }: HelpTextProps) {
  return <p className={`text-sm text-slate-600 ${className}`.trim()}>{children}</p>
}
