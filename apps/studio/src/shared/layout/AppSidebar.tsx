import { NavLink } from 'react-router-dom'
import { Badge } from '../components/Badge'
import { Button } from '../components/Button'
import { useAuth } from '../auth/useAuth'
import { logout } from '../../features/auth/api/authApi'

type NavItem = {
  to: string
  label: string
  soon?: boolean
}

const navItems: NavItem[] = [
  { to: '/dashboard', label: 'Dashboard' },
  { to: '/collections', label: 'Collections' },
  { to: '/pages', label: 'Pages' },
  { to: '/menus', label: 'Navigation' },
  { to: '/seo/redirects', label: 'SEO' },
  { to: '/media', label: 'Media' },
]

type SidebarNavProps = {
  onNavigate?: () => void
}

export function SidebarNav({ onNavigate }: SidebarNavProps) {
  return (
    <nav className="space-y-1">
      {navItems.map((item) => (
        <NavLink
          key={item.to}
          to={item.to}
          onClick={onNavigate}
          className={({ isActive }) =>
            `flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition ${
              isActive ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'
            }`
          }
        >
          <span>{item.label}</span>
          {item.soon && <Badge tone="muted">Soon</Badge>}
        </NavLink>
      ))}
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
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Luma CMS</p>
        <h1 className="mt-1 text-xl font-semibold text-slate-900">Studio</h1>
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
