import { Link, useNavigate, useParams } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { Card } from '../../../shared/components/Card'
import { ErrorAlert } from '../../../shared/components/ErrorAlert'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { Breadcrumbs } from '../../../shared/components/Breadcrumbs'
import { ApiError } from '../../../shared/api/client'
import { formatFieldErrors } from '../../../shared/utils/format'
import { CollectionForm } from '../components/CollectionForm'
import { useCollection, useCreateCollection, useUpdateCollection } from '../hooks/useCollections'

export function CollectionEditPage() {
  const navigate = useNavigate()
  const { slug } = useParams()
  const isNew = !slug || slug === 'new'
  const collectionQuery = useCollection(isNew ? undefined : slug)
  const createMutation = useCreateCollection()
  const updateMutation = useUpdateCollection(slug ?? '')

  const mutation = isNew ? createMutation : updateMutation
  const errorMessage =
    mutation.error instanceof ApiError
      ? formatFieldErrors(mutation.error.errors) || mutation.error.message
      : mutation.error instanceof Error
        ? mutation.error.message
        : null

  return (
    <>
      <PageHeader
        title={isNew ? 'Create collection' : 'Edit collection'}
        breadcrumbs={
          <Breadcrumbs
            items={[
              { label: 'Collections', to: '/collections' },
              { label: isNew ? 'New' : slug ?? 'Edit' },
            ]}
          />
        }
        actions={
          !isNew && slug ? (
            <div className="flex gap-2">
              <Link to={`/collections/${slug}/fields`}>
                <Button variant="secondary">Manage fields</Button>
              </Link>
              <Link to={`/collections/${slug}/entries`}>
                <Button variant="secondary">Manage entries</Button>
              </Link>
            </div>
          ) : undefined
        }
      />

      {!isNew && collectionQuery.isLoading && <LoadingState />}
      {!isNew && collectionQuery.isError && (
        <ErrorAlert message={collectionQuery.error.message} />
      )}

      {(isNew || collectionQuery.data) && (
        <Card>
          {errorMessage && <div className="mb-4"><ErrorAlert message={errorMessage} /></div>}
          <CollectionForm
            key={isNew ? 'new' : collectionQuery.data!.updated_at ?? collectionQuery.data!.slug}
            initialValues={
              isNew
                ? undefined
                : {
                    name: collectionQuery.data!.name,
                    slug: collectionQuery.data!.slug,
                    description: collectionQuery.data!.description ?? '',
                  }
            }
            submitLabel={isNew ? 'Create collection' : 'Save changes'}
            loading={mutation.isPending}
            onSubmit={(values) => {
              if (isNew) {
                createMutation.mutate(values, {
                  onSuccess: (collection) => navigate(`/collections/${collection.slug}/edit`),
                })
                return
              }

              updateMutation.mutate(values, {
                onSuccess: (collection) => {
                  if (collection.slug !== slug) {
                    navigate(`/collections/${collection.slug}/edit`, { replace: true })
                  }
                },
              })
            }}
          />
        </Card>
      )}
    </>
  )
}
