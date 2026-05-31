import { Link } from 'react-router-dom'

type BreadcrumbsProps = {
  items: Array<{ label: string; to?: string }>
}

export function Breadcrumbs({ items }: BreadcrumbsProps) {
  return (
    <nav className="mb-2 text-sm text-slate-500">
      {items.map((item, index) => (
        <span key={`${item.label}-${index}`}>
          {index > 0 && <span className="mx-2">/</span>}
          {item.to ? (
            <Link to={item.to} className="hover:text-slate-900">
              {item.label}
            </Link>
          ) : (
            <span className="text-slate-700">{item.label}</span>
          )}
        </span>
      ))}
    </nav>
  )
}
