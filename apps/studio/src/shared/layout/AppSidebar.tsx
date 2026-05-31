import { NavLink } from 'react-router-dom'
import { Badge } from '../components/Badge'
import { Button } from '../components/Button'
import { useAuth } from '../auth/useAuth'
import { logout } from '../../features/auth/api/authApi'
import { useAdminNavigation } from '../../features/plugins/hooks/useAdminNavigation'
import { isOwner } from '../utils/format'

type NavItem = {
  to: string
  label: string
  soon?: boolean
  external?: boolean
  plugin?: boolean
  ownerOnly?: boolean
}

type NavGroup = {
  label: string
  items: NavItem[]
}

const navGroups: NavGroup[] = [
  {
    label: 'Overview',
    items: [{ to: '/dashboard', label: 'Dashboard' }],
  },
  {
    label: 'Content',
    items: [
      { to: '/pages', label: 'Pages' },
      { to: '/collections', label: 'Collections' },
      { to: '/media', label: 'Media' },
      { to: '/forms', label: 'Forms' },
      { to: '/menus', label: 'Navigation' },
    ],
  },
  {
    label: 'Growth',
    items: [
      { to: '/seo/redirects', label: 'Redirects' },
      { to: '/integrations/webhooks', label: 'Webhooks' },
      { to: '/integrations/tokens', label: 'API Tokens' },
    ],
  },
  {
    label: 'System',
    items: [
      { to: '/plugins', label: 'Plugins' },
      { to: '/settings', label: 'Settings' },
      { to: '/settings/updates', label: 'Updates', ownerOnly: true },
    ],
  },
]

function isExternalUrl(path: string): boolean {
  return path.startsWith('http://') || path.startsWith('https://')
}

function SidebarLink({ item, onNavigate }: { item: NavItem; onNavigate?: () => void }) {
  const className = ({ isActive }: { isActive: boolean }) =>
    `flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition ${
      isActive ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'
    }`

  if (item.external) {
    return (
      <a
        href={item.to}
        target="_blank"
        rel="noreferrer"
        onClick={onNavigate}
        className="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
      >
        <span>{item.label}</span>
        {item.plugin && <Badge tone="muted">Plugin</Badge>}
      </a>
    )
  }

  return (
    <NavLink to={item.to} onClick={onNavigate} className={className}>
      <span>{item.label}</span>
      <span className="flex items-center gap-1">
        {item.plugin && <Badge tone="muted">Plugin</Badge>}
        {item.soon && <Badge tone="muted">Soon</Badge>}
      </span>
    </NavLink>
  )
}

type SidebarNavProps = {
  onNavigate?: () => void
}

export function SidebarNav({ onNavigate }: SidebarNavProps) {
  const { user } = useAuth()
  const owner = isOwner(user)
  const adminNavigationQuery = useAdminNavigation()
  const pluginItems: NavItem[] =
    adminNavigationQuery.data?.map((item) => ({
      to: item.to,
      label: item.label,
      external: isExternalUrl(item.to),
      plugin: true,
    })) ?? []

  return (
    <nav className="space-y-4">
      {navGroups.map((group) => {
        const visibleItems = group.items.filter((item) => !item.ownerOnly || owner)
        if (visibleItems.length === 0) {
          return null
        }

        return (
          <div key={group.label}>
            <p className="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
              {group.label}
            </p>
            <div className="space-y-1">
              {visibleItems.map((item) => (
                <SidebarLink key={item.to} item={item} onNavigate={onNavigate} />
              ))}
            </div>
          </div>
        )
      })}

      {pluginItems.length > 0 && (
        <div>
          <p className="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            Plugins
          </p>
          <div className="space-y-1">
            {pluginItems.map((item) => (
              <SidebarLink key={`${item.to}-${item.label}`} item={item} onNavigate={onNavigate} />
            ))}
          </div>
        </div>
      )}
    </nav>
  )
}

type AppSidebarProps = {
  onNavigate?: () => void
}

export function AppSidebar({ onNavigate }: AppSidebarProps) {
  const { user, logoutSession, token } = useAuth()

  async function handleLogout() {
    if (token) {
      try {
        await logout(token)
      } catch {
        // Clear local session even if API logout fails.
      }
    }

    logoutSession()
  }

  return (
    <aside className="border-b border-slate-200 bg-white px-4 py-6 lg:min-h-screen lg:border-b-0 lg:border-r lg:w-[var(--luma-sidebar-width)]">
      <div className="mb-8">
        <h1 className="text-xl font-semibold text-slate-900">Luma Studio</h1>
        <p className="mt-1 text-xs text-slate-500">Manage your site</p>
      </div>
      <SidebarNav onNavigate={onNavigate} />
      <div className="mt-8 border-t border-slate-200 pt-4">
        <p className="text-sm font-medium text-slate-900">{user?.name}</p>
        <p className="text-xs text-slate-500">{user?.email}</p>
        <div className="mt-2 flex flex-wrap gap-1">
          {user?.roles.map((role) => (
            <Badge key={role}>{role}</Badge>
          ))}
        </div>
        <Button variant="ghost" className="mt-4 w-full" onClick={() => void handleLogout()}>
          Log out
        </Button>
      </div>
    </aside>
  )
}
