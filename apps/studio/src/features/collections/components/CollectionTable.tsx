import { Link } from 'react-router-dom'
import { Button } from '../../../shared/components/Button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeaderCell,
  TableRow,
} from '../../../shared/components/Table'
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
    <Table>
      <TableHead>
        <TableRow>
          <TableHeaderCell>Name</TableHeaderCell>
          <TableHeaderCell>Slug</TableHeaderCell>
          <TableHeaderCell>Schema</TableHeaderCell>
          <TableHeaderCell>Updated</TableHeaderCell>
          <TableHeaderCell>Actions</TableHeaderCell>
        </TableRow>
      </TableHead>
      <TableBody>
        {collections.map((collection) => (
          <TableRow key={collection.id}>
            <TableCell className="font-medium text-slate-900">{collection.name}</TableCell>
            <TableCell className="text-slate-600">{collection.slug}</TableCell>
            <TableCell className="text-slate-600">v{collection.schema_version}</TableCell>
            <TableCell className="text-slate-600">{formatDate(collection.updated_at)}</TableCell>
            <TableCell>
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
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}
