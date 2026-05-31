import type { ReactNode } from 'react'
import { Card } from '../components/Card'
import { LoadingState } from '../components/LoadingState'
import { PageHeader } from '../components/PageHeader'

type ListPageProps = {
  title: string
  description?: string
  actions?: ReactNode
  breadcrumbs?: ReactNode
  loading?: boolean
  loadingMessage?: string
  error?: ReactNode
  empty?: ReactNode
  children?: ReactNode
}

export function ListPage({
  title,
  description,
  actions,
  breadcrumbs,
  loading,
  loadingMessage,
  error,
  empty,
  children,
}: ListPageProps) {
  return (
    <>
      <PageHeader
        title={title}
        description={description}
        actions={actions}
        breadcrumbs={breadcrumbs}
      />
      {loading && <LoadingState message={loadingMessage} />}
      {error}
      {!loading && empty}
      {!loading && children}
    </>
  )
}

type FormPageProps = {
  title: string
  description?: string
  actions?: ReactNode
  breadcrumbs?: ReactNode
  beforeForm?: ReactNode
  children: ReactNode
}

export function FormPage({
  title,
  description,
  actions,
  breadcrumbs,
  beforeForm,
  children,
}: FormPageProps) {
  return (
    <>
      <PageHeader
        title={title}
        description={description}
        actions={actions}
        breadcrumbs={breadcrumbs}
      />
      {beforeForm}
      <Card>{children}</Card>
    </>
  )
}
