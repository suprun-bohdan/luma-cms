import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { Input } from '../../../shared/components/Input'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { ApiError } from '../../../shared/api/client'
import { formatFieldErrors } from '../../../shared/utils/format'
import { useCreateMenu, useMenu, useUpdateMenu } from '../hooks/useMenus'
import type { Menu, MenuFormValues } from '../schemas/menu'

type ItemRow = MenuFormValues['items'][number]

const emptyItem = (sortOrder: number): ItemRow => ({
  label: '',
  page_slug: '',
  url: '',
  sort_order: sortOrder,
})

const menuLabels: Record<string, string> = {
  header: 'Header',
  footer: 'Footer',
}

function menuToFormValues(menu?: Menu): { name: string; items: ItemRow[] } {
  if (!menu) {
    return { name: 'Menu', items: [emptyItem(0)] }
  }

  return {
    name: menu.name,
    items: menu.items?.length
      ? menu.items.map((item, index) => ({
          label: item.label,
          page_slug: item.page_slug ?? '',
          url: item.url ?? '',
          sort_order: item.sort_order ?? index,
        }))
      : [emptyItem(0)],
  }
}

type MenuFormProps = {
  menuSlug: string
  initialValues: { name: string; items: ItemRow[] }
  isNew: boolean
  loading: boolean
  errorMessage: string | null
  onSave: (payload: Record<string, unknown>) => void
}

function MenuForm({
  menuSlug,
  initialValues,
  isNew,
  loading,
  errorMessage,
  onSave,
}: MenuFormProps) {
  const [name, setName] = useState(initialValues.name)
  const [items, setItems] = useState(initialValues.items)

  function updateItem(index: number, patch: Partial<ItemRow>) {
    setItems((current) =>
      current.map((item, itemIndex) => (itemIndex === index ? { ...item, ...patch } : item)),
    )
  }

  function addItem() {
    setItems((current) => [...current, emptyItem(current.length)])
  }

  function removeItem(index: number) {
    setItems((current) => current.filter((_, itemIndex) => itemIndex !== index))
  }

  function moveItem(index: number, direction: -1 | 1) {
    setItems((current) => {
      const target = index + direction
      if (target < 0 || target >= current.length) {
        return current
      }

      const next = [...current]
      const temp = next[index]
      next[index] = next[target]!
      next[target] = temp!
      return next
    })
  }

  function handleSave() {
    onSave({
      name,
      slug: menuSlug,
      items: items
        .filter((item) => item.label.trim() !== '')
        .map((item, index) => ({
          label: item.label.trim(),
          page_slug: item.page_slug?.trim() || null,
          url: item.url?.trim() || null,
          sort_order: index,
        })),
    })
  }

  return (
    <Card>
      {errorMessage && (
        <div className="mb-4">
          <ErrorAlert message={errorMessage} />
        </div>
      )}

      <Input
        label="Menu name"
        id="menu-name"
        value={name}
        onChange={(event) => setName(event.target.value)}
      />

      <div className="space-y-4">
        {items.map((item, index) => (
          <div
            key={index}
            className="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[1fr_1fr_1fr_auto]"
          >
            <Input
              label="Label"
              value={item.label}
              onChange={(event) => updateItem(index, { label: event.target.value })}
            />
            <Input
              label="Page slug"
              placeholder="about-us"
              className="font-mono"
              value={item.page_slug ?? ''}
              onChange={(event) => updateItem(index, { page_slug: event.target.value })}
            />
            <Input
              label="External URL"
              placeholder="https://example.com"
              value={item.url ?? ''}
              onChange={(event) => updateItem(index, { url: event.target.value })}
            />
            <div className="flex flex-wrap items-end gap-2">
              <Button
                type="button"
                variant="ghost"
                disabled={index === 0}
                onClick={() => moveItem(index, -1)}
              >
                Up
              </Button>
              <Button
                type="button"
                variant="ghost"
                disabled={index === items.length - 1}
                onClick={() => moveItem(index, 1)}
              >
                Down
              </Button>
              <Button type="button" variant="ghost" onClick={() => removeItem(index)}>
                Remove
              </Button>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        <Button type="button" variant="secondary" onClick={addItem}>
          Add item
        </Button>
        <Button type="button" disabled={loading} onClick={handleSave}>
          {loading ? 'Saving…' : isNew ? 'Create menu' : 'Save menu'}
        </Button>
      </div>
    </Card>
  )
}

export function MenuEditorPage() {
  const { menuSlug = 'header' } = useParams()
  const menuQuery = useMenu(menuSlug)
  const createMutation = useCreateMenu()
  const updateMutation = useUpdateMenu(menuSlug)

  const isNew = menuQuery.isError && menuQuery.error instanceof ApiError && menuQuery.error.status === 404
  const mutation = isNew ? createMutation : updateMutation
  const errorMessage =
    mutation.error instanceof ApiError
      ? formatFieldErrors(mutation.error.errors) || mutation.error.message
      : mutation.error instanceof Error
        ? mutation.error.message
        : null

  const menuLabel = menuLabels[menuSlug] ?? menuSlug

  if (menuQuery.isLoading) {
    return <LoadingState message="Loading menu…" />
  }

  if (menuQuery.isError && !isNew) {
    return <ErrorAlert message={menuQuery.error.message} />
  }

  const formKey = isNew ? 'new' : (menuQuery.data?.updated_at ?? menuSlug)

  return (
    <>
      <PageHeader
        title={`${menuLabel} navigation`}
        breadcrumbs={
          <Breadcrumbs
            items={[
              { label: 'Navigation', to: '/menus' },
              { label: menuLabel },
            ]}
          />
        }
        description="Use page slug for internal pages or URL for external links. Reorder with Up/Down."
        actions={
          <Link to="/menus">
            <Button variant="secondary">All menus</Button>
          </Link>
        }
      />

      <MenuForm
        key={formKey}
        menuSlug={menuSlug}
        initialValues={menuToFormValues(isNew ? undefined : menuQuery.data)}
        isNew={isNew}
        loading={mutation.isPending}
        errorMessage={errorMessage}
        onSave={(payload) => {
          if (isNew) {
            createMutation.mutate(payload, {
              onSuccess: () => void menuQuery.refetch(),
            })
            return
          }

          updateMutation.mutate(payload, {
            onSuccess: () => void menuQuery.refetch(),
          })
        }}
      />
    </>
  )
}
