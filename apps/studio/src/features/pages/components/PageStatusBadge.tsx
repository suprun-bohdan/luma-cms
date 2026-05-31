import { Badge } from '../../../shared/components/Badge'
import type { PageStatus } from '../schemas/page'

const toneMap: Record<PageStatus, 'default' | 'success' | 'warning' | 'muted'> = {
  draft: 'warning',
  published: 'success',
  archived: 'muted',
}

export function PageStatusBadge({ status }: { status: PageStatus }) {
  return <Badge tone={toneMap[status]}>{status}</Badge>
}
