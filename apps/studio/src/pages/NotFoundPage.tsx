import { Link } from 'react-router-dom'
import { Button } from '../shared/components/Button'
import { Card } from '../shared/components/Card'

export function NotFoundPage() {
  return (
    <div className="flex min-h-[60vh] items-center justify-center">
      <Card className="max-w-md text-center">
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">404</p>
        <h1 className="mt-2 text-2xl font-semibold text-slate-900">Page not found</h1>
        <p className="mt-2 text-sm text-slate-600">
          The page you requested does not exist in Studio.
        </p>
        <Link to="/dashboard" className="mt-6 inline-block">
          <Button>Back to dashboard</Button>
        </Link>
      </Card>
    </div>
  )
}
