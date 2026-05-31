import { Badge } from '../../../shared/components/Badge'
import type { EntryStatus } from '../schemas/entry'

const toneMap: Record<EntryStatus, 'default' | 'success' | 'warning' | 'muted'> = {
  draft: 'warning',
  published: 'success',
  archived: 'muted',
}

export function EntryStatusBadge({ status }: { status: EntryStatus }) {
  return <Badge tone={toneMap[status]}>{status}</Badge>
}
