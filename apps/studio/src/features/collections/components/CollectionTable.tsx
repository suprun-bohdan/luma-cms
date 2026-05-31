import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import { canDeleteContent, formatDate } from '../../../shared/utils/format'
import { useAuth } from '../../../shared/auth/useAuth'
import type { Collection } from '../schemas/collection'

type CollectionTableProps = {
  collections: Collection[]
  onDelete: (slug: string) => void
}

export function CollectionTable({ collections, onDelete }: CollectionTableProps) {
  const { user } = useAuth()
  const canDelete = canDeleteContent(user)

  return (
    <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
      <table className="min-w-full text-left text-sm">
        <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
          <tr>
            <th className="px-4 py-3 font-medium">Name</th>
            <th className="px-4 py-3 font-medium">Slug</th>
            <th className="px-4 py-3 font-medium">Schema</th>
            <th className="px-4 py-3 font-medium">Updated</th>
            <th className="px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          {collections.map((collection) => (
            <tr key={collection.id} className="border-b border-slate-100 last:border-b-0">
              <td className="px-4 py-3 font-medium text-slate-900">{collection.name}</td>
              <td className="px-4 py-3 text-slate-600">{collection.slug}</td>
              <td className="px-4 py-3 text-slate-600">v{collection.schema_version}</td>
              <td className="px-4 py-3 text-slate-600">{formatDate(collection.updated_at)}</td>
              <td className="px-4 py-3">
                <div className="flex flex-wrap gap-2">
                  <Link to={`/collections/${collection.slug}/edit`}>
                    <Button variant="secondary">Edit</Button>
                  </Link>
                  <Link to={`/collections/${collection.slug}/fields`}>
                    <Button variant="ghost">Fields</Button>
                  </Link>
                  <Link to={`/collections/${collection.slug}/entries`}>
                    <Button variant="ghost">Entries</Button>
                  </Link>
                  {canDelete && (
                    <Button variant="danger" onClick={() => onDelete(collection.slug)}>
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
  )
}
