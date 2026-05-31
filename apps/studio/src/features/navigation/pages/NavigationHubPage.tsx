import { Link } from 'react-router-dom'
import { Card } from '../../../shared/components/Card'
import { PageHeader } from '../../../shared/components/PageHeader'

const menus = [
  {
    slug: 'header',
    name: 'Header',
    description: 'Primary navigation shown at the top of public pages.',
  },
  {
    slug: 'footer',
    name: 'Footer',
    description: 'Secondary links shown at the bottom of public pages.',
  },
]

export function NavigationHubPage() {
  return (
    <>
      <PageHeader
        title="Navigation"
        description="Manage header and footer menus for the public site."
      />

      <div className="grid gap-4 md:grid-cols-2">
        {menus.map((menu) => (
          <Card key={menu.slug}>
            <h2 className="text-lg font-semibold text-slate-900">{menu.name}</h2>
            <p className="mt-1 text-sm text-slate-600">{menu.description}</p>
            <Link
              to={`/menus/${menu.slug}`}
              className="mt-4 inline-block text-sm font-medium text-slate-900 underline"
            >
              Edit {menu.name.toLowerCase()} menu
            </Link>
          </Card>
        ))}
      </div>
    </>
  )
}
