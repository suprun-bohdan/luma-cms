import type { HTMLAttributes, ReactNode, TdHTMLAttributes, ThHTMLAttributes } from 'react'

type TableProps = HTMLAttributes<HTMLTableElement> & {
  children: ReactNode
}

export function Table({ children, className = '', ...props }: TableProps) {
  return (
    <div className="overflow-x-auto rounded-xl border border-[var(--luma-color-border)] bg-[var(--luma-color-surface)]">
      <table className={`min-w-full text-left text-sm ${className}`} {...props}>
        {children}
      </table>
    </div>
  )
}

export function TableHead({ children, className = '', ...props }: HTMLAttributes<HTMLTableSectionElement>) {
  return (
    <thead
      className={`border-b border-[var(--luma-color-border)] bg-[var(--luma-color-surface-muted)] text-[var(--luma-color-text-muted)] ${className}`}
      {...props}
    >
      {children}
    </thead>
  )
}

export function TableBody({ children, className = '', ...props }: HTMLAttributes<HTMLTableSectionElement>) {
  return (
    <tbody className={className} {...props}>
      {children}
    </tbody>
  )
}

export function TableRow({ children, className = '', ...props }: HTMLAttributes<HTMLTableRowElement>) {
  return (
    <tr className={`border-b border-slate-100 last:border-b-0 ${className}`} {...props}>
      {children}
    </tr>
  )
}

export function TableCell({ children, className = '', ...props }: TdHTMLAttributes<HTMLTableCellElement>) {
  return (
    <td className={`px-4 py-3 ${className}`} {...props}>
      {children}
    </td>
  )
}

export function TableHeaderCell({
  children,
  className = '',
  ...props
}: ThHTMLAttributes<HTMLTableCellElement>) {
  return (
    <th className={`px-4 py-3 font-medium ${className}`} {...props}>
      {children}
    </th>
  )
}
