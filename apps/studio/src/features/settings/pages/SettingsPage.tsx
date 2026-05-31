import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { HelpText } from '../../../shared/components/HelpText'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { useAuth } from '../../../shared/auth/useAuth'
import { isOwner } from '../../../shared/utils/format'
import { useSettings, useUpdateSettings } from '../hooks/useSettings'

const docLinks = [
  { href: '/docs/installation.md', label: 'Installation guide' },
  { href: '/docs/production.md', label: 'Production checklist' },
  { href: '/docs/security.md', label: 'Plugin security' },
  { href: '/docs/integrations.md', label: 'Integrations' },
] as const

export function SettingsPage() {
  const { user } = useAuth()
  const owner = isOwner(user)
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
      <PageHeader title="Settings" description="Site name and tagline shown on your public site." />
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

        <div className="border-t border-slate-200 pt-4">
          <p className="text-sm font-medium text-slate-900">Release updates</p>
          {!owner && (
            <HelpText className="mt-1">
              Only the site owner can run database updates after uploading a new release.
            </HelpText>
          )}
          <Link to="/settings/updates" className="mt-2 inline-block text-sm text-slate-600 underline">
            Open release updates
          </Link>
        </div>

        <div className="border-t border-slate-200 pt-4">
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Documentation</p>
          <ul className="mt-2 space-y-1">
            {docLinks.map((link) => (
              <li key={link.href}>
                <a
                  href={link.href}
                  target="_blank"
                  rel="noreferrer"
                  className="text-sm text-slate-600 underline"
                >
                  {link.label}
                </a>
              </li>
            ))}
          </ul>
        </div>
      </Card>
    </>
  )
}
