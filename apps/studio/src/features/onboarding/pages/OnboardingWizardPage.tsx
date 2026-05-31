import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Badge } from '../../../shared/components/Badge'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { useOnboardingActions, useOnboardingJournal, useOnboardingProgress } from '../hooks/useOnboarding'

const industries = [
  { value: 'technology', label: 'Technology' },
  { value: 'retail', label: 'Retail' },
  { value: 'services', label: 'Professional services' },
  { value: 'nonprofit', label: 'Non-profit' },
  { value: 'other', label: 'Other' },
] as const

const presets = [
  {
    value: 'business' as const,
    label: 'Business',
    description: 'Landing page, contact form, and navigation.',
  },
  {
    value: 'blog' as const,
    label: 'Blog',
    description: 'Editorial hero and content blocks for articles.',
  },
  {
    value: 'portfolio' as const,
    label: 'Portfolio',
    description: 'Showcase projects with a focused hero section.',
  },
]

function stepIndexFromProgress(steps: Array<{ status: string }>): number {
  const currentIndex = steps.findIndex((step) => step.status === 'current')
  if (currentIndex >= 0) {
    return currentIndex
  }

  const firstPending = steps.findIndex((step) => step.status === 'pending')
  return firstPending >= 0 ? firstPending : steps.length - 1
}

export function OnboardingWizardPage() {
  const navigate = useNavigate()
  const progressQuery = useOnboardingProgress()
  const journalQuery = useOnboardingJournal(true)
  const {
    welcomeMutation,
    siteTypeMutation,
    starterMutation,
    integrationsMutation,
    finishMutation,
  } = useOnboardingActions()

  const [siteTitle, setSiteTitle] = useState('My Luma Site')
  const [industry, setIndustry] = useState<(typeof industries)[number]['value']>('technology')
  const [selectedPreset, setSelectedPreset] = useState<(typeof presets)[number]['value']>('business')
  const [error, setError] = useState<string | null>(null)

  const stepIndex = useMemo(
    () => stepIndexFromProgress(progressQuery.data?.steps ?? []),
    [progressQuery.data?.steps],
  )
  const steps = progressQuery.data?.steps ?? []
  const progressPercent = progressQuery.data?.percent ?? 0

  useEffect(() => {
    if (progressQuery.data?.completed) {
      navigate('/dashboard', { replace: true })
    }
  }, [navigate, progressQuery.data?.completed])

  async function runStep(action: () => Promise<unknown>) {
    setError(null)
    try {
      await action()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Onboarding step failed')
    }
  }

  if (progressQuery.isLoading) {
    return <LoadingState message="Loading onboarding…" />
  }

  return (
    <div className="min-h-screen bg-slate-50 px-4 py-10">
      <div className="mx-auto max-w-3xl space-y-6">
        <PageHeader
          title="Welcome to Luma"
          description="First-login onboarding — site type, starter content, and workspace preferences."
        />

        <Card>
          <div className="mb-4 flex items-center justify-between gap-4">
            <p className="text-sm font-medium text-slate-700">
              Step {Math.min(stepIndex + 1, steps.length)} of {steps.length}: {steps[stepIndex]?.label ?? 'Setup'}
            </p>
            <Badge tone="muted">{progressPercent}%</Badge>
          </div>
          <div className="h-2 overflow-hidden rounded-full bg-slate-200">
            <div className="h-full bg-slate-900 transition-all" style={{ width: `${progressPercent}%` }} />
          </div>
        </Card>

        {error && <ErrorAlert message={error} />}

        {stepIndex === 0 && (
          <Card className="space-y-4">
            <Input label="Site name" value={siteTitle} onChange={(event) => setSiteTitle(event.target.value)} />
            <label className="block text-sm font-medium text-slate-700">
              Industry
              <select
                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"
                value={industry}
                onChange={(event) => setIndustry(event.target.value as typeof industry)}
              >
                {industries.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </label>
            <div className="flex justify-end">
              <Button
                disabled={welcomeMutation.isPending}
                onClick={() =>
                  void runStep(() =>
                    welcomeMutation.mutateAsync({
                      site_title: siteTitle,
                      industry,
                    }),
                  )
                }
              >
                Continue
              </Button>
            </div>
          </Card>
        )}

        {stepIndex === 1 && (
          <Card className="space-y-4">
            <p className="text-sm text-slate-600">Choose a starter layout for your public site.</p>
            <div className="grid gap-3">
              {presets.map((preset) => (
                <label
                  key={preset.value}
                  className={`cursor-pointer rounded-lg border p-4 ${
                    selectedPreset === preset.value ? 'border-slate-900 bg-white' : 'border-slate-200'
                  }`}
                >
                  <div className="flex items-start gap-3">
                    <input
                      type="radio"
                      name="preset"
                      checked={selectedPreset === preset.value}
                      onChange={() => setSelectedPreset(preset.value)}
                    />
                    <span>
                      <span className="block font-medium text-slate-900">{preset.label}</span>
                      <span className="mt-1 block text-sm text-slate-600">{preset.description}</span>
                    </span>
                  </div>
                </label>
              ))}
            </div>
            <div className="flex justify-end">
              <Button
                disabled={siteTypeMutation.isPending}
                onClick={() => void runStep(() => siteTypeMutation.mutateAsync(selectedPreset))}
              >
                Continue
              </Button>
            </div>
          </Card>
        )}

        {stepIndex === 2 && (
          <Card className="space-y-4">
            <p className="text-sm text-slate-600">
              Creates a published home page, navigation menus, and sample blocks using your chosen
              preset. You can change everything later in Pages and Navigation.
            </p>
            <div className="flex justify-end">
              <Button
                disabled={starterMutation.isPending}
                onClick={() => void runStep(() => starterMutation.mutateAsync())}
              >
                Install starter content
              </Button>
            </div>
          </Card>
        )}

        {stepIndex === 3 && (
          <Card className="space-y-4">
            <p className="text-sm text-slate-600">
              Webhooks and API tokens are available later under Integrations. You can skip this step for now.
            </p>
            <div className="flex items-center justify-between gap-2">
              <Link to="/integrations/webhooks" className="text-sm text-slate-600 underline">
                Open integrations
              </Link>
              <Button
                disabled={integrationsMutation.isPending}
                onClick={() => void runStep(() => integrationsMutation.mutateAsync())}
              >
                Skip for now
              </Button>
            </div>
          </Card>
        )}

        {stepIndex >= 4 && (
          <Card className="space-y-4">
            <p className="text-sm text-slate-600">
              Your workspace is ready. Visit the public home page or continue in Studio.
            </p>
            <div className="flex flex-wrap gap-2">
              <a href="/p/home" className="inline-flex">
                <Button variant="secondary">View public site</Button>
              </a>
              <Button
                disabled={finishMutation.isPending}
                onClick={() =>
                  void runStep(async () => {
                    await finishMutation.mutateAsync()
                    navigate('/dashboard', { replace: true })
                  })
                }
              >
                Go to dashboard
              </Button>
            </div>
          </Card>
        )}

        <Card>
          <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Onboarding log</p>
          <div className="max-h-48 space-y-2 overflow-y-auto text-sm">
            {(journalQuery.data ?? []).length === 0 && (
              <p className="text-slate-500">Steps you complete here appear in this log.</p>
            )}
            {(journalQuery.data ?? []).map((entry) => (
              <div key={entry.id} className="rounded border border-slate-200 px-3 py-2">
                <div className="flex items-center justify-between gap-2">
                  <span className="font-medium">{entry.step}</span>
                  <Badge tone="muted">{entry.status}</Badge>
                </div>
                <p className="text-slate-600">{entry.message}</p>
              </div>
            ))}
          </div>
        </Card>
      </div>
    </div>
  )
}
