export function LoadingState({ message = 'Loading…' }: { message?: string }) {
  return <p className="text-sm text-[var(--luma-color-text-muted)]">{message}</p>
}
