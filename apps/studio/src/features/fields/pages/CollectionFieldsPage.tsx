import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ConfirmDialog } from '../../../shared/components/ConfirmDialog'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { canDeleteContent, formatDate } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import { useCollection } from '../../collections/hooks/useCollections'
import { FieldForm } from '../components/FieldForm'
import {
  useCreateField,
  useDeleteField,
  useFields,
  useUpdateField,
} from '../hooks/useFields'
import type { Field } from '../schemas/field'
import { fieldTypeLabels } from '../schemas/field'

export function CollectionFieldsPage() {
  const { slug = '' } = useParams()
  const { user } = useAuth()
  const collectionQuery = useCollection(slug)
  const fieldsQuery = useFields(slug)
  const createMutation = useCreateField(slug)
  const deleteMutation = useDeleteField(slug)
  const [editingField, setEditingField] = useState<Field | null>(null)
  const updateMutation = useUpdateField(slug, editingField?.slug ?? '')
  const [showCreate, setShowCreate] = useState(false)
  const [pendingDelete, setPendingDelete] = useState<Field | null>(null)
  const canDelete = canDeleteContent(user)

  return (
    <>
      <PageHeader
        title="Fields"
        description={
          collectionQuery.data
            ? `${collectionQuery.data.name} · schema v${collectionQuery.data.schema_version}`
            : 'Collection field schema'
        }
        breadcrumbs={
          <Breadcrumbs
            items={[
              { label: 'Collections', to: '/collections' },
              { label: slug, to: `/collections/${slug}/edit` },
              { label: 'Fields' },
            ]}
          />
        }
        actions={
          <div className="flex gap-2">
            <Link to={`/collections/${slug}/entries`}>
              <Button variant="secondary">Entries</Button>
            </Link>
            <Button onClick={() => setShowCreate((value) => !value)}>
              {showCreate ? 'Close form' : 'Add field'}
            </Button>
          </div>
        }
      />

      {fieldsQuery.isLoading && <LoadingState message="Loading fields…" />}
      {fieldsQuery.isError && <ErrorAlert message={fieldsQuery.error.message} />}

      {showCreate && (
        <Card className="mb-6">
          <h2 className="mb-4 text-lg font-medium">New field</h2>
          <FieldForm
            submitLabel="Create field"
            loading={createMutation.isPending}
            onSubmit={(values) => {
              createMutation.mutate(values, {
                onSuccess: () => {
                  setShowCreate(false)
                },
              })
            }}
          />
        </Card>
      )}

      {fieldsQuery.data?.length === 0 && (
        <EmptyState
          title="No fields yet"
          description="Add fields to define the entry schema for this collection."
        />
      )}

      {fieldsQuery.data && fieldsQuery.data.length > 0 && (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
          <table className="min-w-full text-left text-sm">
            <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
              <tr>
                <th className="px-4 py-3 font-medium">Name</th>
                <th className="px-4 py-3 font-medium">Slug</th>
                <th className="px-4 py-3 font-medium">Type</th>
                <th className="px-4 py-3 font-medium">Required</th>
                <th className="px-4 py-3 font-medium">Order</th>
                <th className="px-4 py-3 font-medium">Updated</th>
                <th className="px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              {fieldsQuery.data.map((field) => (
                <tr key={field.id} className="border-b border-slate-100 last:border-b-0">
                  <td className="px-4 py-3 font-medium">{field.name}</td>
                  <td className="px-4 py-3">{field.slug}</td>
                  <td className="px-4 py-3">{fieldTypeLabels[field.type]}</td>
                  <td className="px-4 py-3">{field.required ? 'Yes' : 'No'}</td>
                  <td className="px-4 py-3">{field.sort_order}</td>
                  <td className="px-4 py-3">{formatDate(field.updated_at)}</td>
                  <td className="px-4 py-3">
                    <div className="flex gap-2">
                      <Button variant="secondary" onClick={() => setEditingField(field)}>
                        Edit
                      </Button>
                      {canDelete && (
                        <Button variant="danger" onClick={() => setPendingDelete(field)}>
                          Delete
                        </Button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {editingField && (
        <div className="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4">
          <Card className="max-h-[90vh] w-full max-w-xl overflow-y-auto">
            <h2 className="mb-4 text-lg font-medium">Edit field</h2>
            <FieldForm
              key={editingField.id}
              initialValues={{
                name: editingField.name,
                slug: editingField.slug,
                type: editingField.type,
                required: editingField.required,
                sort_order: editingField.sort_order,
                config: editingField.config,
              }}
              submitLabel="Save field"
              loading={updateMutation.isPending}
              onSubmit={(values) => {
                updateMutation.mutate(values, {
                  onSuccess: () => setEditingField(null),
                })
              }}
            />
            <Button variant="ghost" className="mt-4" onClick={() => setEditingField(null)}>
              Cancel
            </Button>
          </Card>
        </div>
      )}

      <ConfirmDialog
        open={pendingDelete !== null}
        title="Delete field"
        description="This removes the field from the collection schema."
        confirmLabel="Delete"
        loading={deleteMutation.isPending}
        onCancel={() => setPendingDelete(null)}
        onConfirm={() => {
          if (!pendingDelete) {
            return
          }

          deleteMutation.mutate(pendingDelete.slug, {
            onSuccess: () => setPendingDelete(null),
          })
        }}
      />
    </>
  )
}
