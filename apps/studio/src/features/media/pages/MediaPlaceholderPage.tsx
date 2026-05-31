import { PageHeader } from '../../../shared/components/PageHeader'
import { EmptyState } from '../../../shared/components/EmptyState'
import { Card } from '../../../shared/components/Card'

export function MediaPlaceholderPage() {
  return (
    <>
      <PageHeader
        title="Media"
        description="Upload and manage assets for your content."
      />
      <Card>
        <EmptyState
          title="Media library coming soon"
          description="Upload, browse, and attach media requires the Media Core backend slice. This placeholder keeps navigation stable until that module ships."
        />
      </Card>
    </>
  )
}
