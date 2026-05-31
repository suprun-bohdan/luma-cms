import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { useSettings, useUpdateSettings } from '../hooks/useSettings'

export function SettingsPage() {
  const settingsQuery = useSettings()
  const updateMutation = useUpdateSettings()
  const [draft, setDraft] = useState<{ title: string; tagline: string } | null>(null)

  if (settingsQuery.isLoading || !settingsQuery.data) {
    return <LoadingState message="Loading settings…" />
  }

  const values = draft ?? {
    title: settingsQuery.data.site.title,
    tagline: settingsQuery.data.site.tagline,
  }

  return (
    <>
      <PageHeader title="Settings" description="Global site configuration." />
      {updateMutation.isError && <ErrorAlert message={updateMutation.error.message} />}
      <Card className="max-w-xl space-y-4">
        <Input
          label="Site title"
          value={values.title}
          onChange={(event) => setDraft({ ...values, title: event.target.value })}
        />
        <Input
          label="Tagline"
          value={values.tagline}
          onChange={(event) => setDraft({ ...values, tagline: event.target.value })}
        />
        <Button
          onClick={() =>
            updateMutation.mutate(
              {
                'site.title': values.title,
                'site.tagline': values.tagline,
              },
              {
                onSuccess: () => setDraft(null),
              },
            )
          }
          disabled={updateMutation.isPending}
        >
          Save settings
        </Button>
        <Link to="/settings/updates" className="inline-block text-sm text-slate-600 underline">
          Release updates
        </Link>
      </Card>
    </>
  )
}
