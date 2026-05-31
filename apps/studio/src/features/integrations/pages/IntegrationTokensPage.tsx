import { useState } from 'react'
import { ApiError } from '../../../shared/api/client'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { Checkbox } from '../../../shared/components/Checkbox'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { Input } from '../../../shared/components/Input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
import { ListPage } from '../../../shared/layout'
import { formatFieldErrors } from '../../../shared/utils/format'
import {
  useIntegrationScopes,
  useIntegrationTokenActions,
  useIntegrationTokens,
} from '../hooks/useIntegrations'
import type { CreatedIntegrationToken } from '../schemas/integration'

export function IntegrationTokensPage() {
  const tokensQuery = useIntegrationTokens()
  const scopesQuery = useIntegrationScopes()
  const actions = useIntegrationTokenActions()
  const [name, setName] = useState('')
  const [abilities, setAbilities] = useState<string[]>([])
  const [createdToken, setCreatedToken] = useState<CreatedIntegrationToken | null>(null)

  const createError =
    actions.create.error instanceof ApiError
      ? formatFieldErrors(actions.create.error.errors) || actions.create.error.message
      : actions.create.error instanceof Error
        ? actions.create.error.message
        : null

  function toggleAbility(ability: string) {
    setAbilities((current) =>
      current.includes(ability) ? current.filter((item) => item !== ability) : [...current, ability],
    )
  }

  return (
    <ListPage
      title="Integration tokens"
      description="Machine-to-machine API access with scoped abilities (separate from admin login)."
      loading={tokensQuery.isLoading}
      loadingMessage="Loading tokens…"
      error={
        tokensQuery.isError ? (
          <ErrorAlert
            message={
              tokensQuery.error instanceof Error
                ? tokensQuery.error.message
                : 'Failed to load tokens'
            }
          />
        ) : undefined
      }
    >
      <Card className="mb-8">
        <h2 className="text-lg font-semibold text-slate-900">Create token</h2>
        <p className="mt-1 text-sm text-slate-600">
          The plain token is shown once. Store it securely before leaving this page.
        </p>

        {createError && (
          <div className="mt-4">
            <ErrorAlert message={createError} />
          </div>
        )}

        {createdToken && (
          <div className="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            <p className="font-medium">Token created — copy now:</p>
            <code className="mt-2 block break-all font-mono text-xs">{createdToken.plain_text_token}</code>
          </div>
        )}

        <form
          className="mt-4 space-y-4"
          onSubmit={(event) => {
            event.preventDefault()
            actions.create.mutate(
              { name, abilities },
              {
                onSuccess: (token) => {
                  setCreatedToken(token)
                  setName('')
                  setAbilities([])
                },
              },
            )
          }}
        >
          <Input label="Name" value={name} onChange={(event) => setName(event.target.value)} required />

          {scopesQuery.data && (
            <fieldset className="space-y-2">
              <legend className="text-sm font-medium text-slate-900">Abilities</legend>
              {scopesQuery.data.map((ability) => (
                <Checkbox
                  key={ability}
                  label={ability}
                  checked={abilities.includes(ability)}
                  onChange={() => toggleAbility(ability)}
                />
              ))}
            </fieldset>
          )}

          <Button type="submit" disabled={actions.create.isPending || abilities.length === 0}>
            Generate token
          </Button>
        </form>
      </Card>

      {tokensQuery.data && tokensQuery.data.length > 0 && (
        <Table>
          <TableHead>
            <TableRow>
              <TableHeaderCell>Name</TableHeaderCell>
              <TableHeaderCell>Prefix</TableHeaderCell>
              <TableHeaderCell>Abilities</TableHeaderCell>
              <TableHeaderCell>Last used</TableHeaderCell>
              <TableHeaderCell />
            </TableRow>
          </TableHead>
          <TableBody>
            {tokensQuery.data.map((token) => (
              <TableRow key={token.id}>
                <TableCell className="font-medium text-slate-900">{token.name}</TableCell>
                <TableCell className="font-mono text-xs">{token.token_prefix}…</TableCell>
                <TableCell className="text-xs text-slate-600">{token.abilities.join(', ')}</TableCell>
                <TableCell className="text-xs text-slate-600">{token.last_used_at ?? 'Never'}</TableCell>
                <TableCell className="text-right">
                  <Button
                    variant="danger"
                    disabled={actions.revoke.isPending}
                    onClick={() => actions.revoke.mutate(token.id)}
                  >
                    Revoke
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}
    </ListPage>
  )
}
