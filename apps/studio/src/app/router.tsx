import { Navigate, createBrowserRouter } from 'react-router-dom'
import { ProtectedLayout } from './ProtectedLayout'
import { LoginPage } from '../features/auth/pages/LoginPage'
import { CollectionsListPage } from '../features/collections/pages/CollectionsListPage'
import { CollectionEditPage } from '../features/collections/pages/CollectionEditPage'
import { CollectionFieldsPage } from '../features/fields/pages/CollectionFieldsPage'
import { CollectionEntriesPage } from '../features/entries/pages/CollectionEntriesPage'
import { EntryEditPage } from '../features/entries/pages/EntryEditPage'
import { EntryPreviewPage } from '../features/preview/pages/EntryPreviewPage'
import { MediaLibraryPage } from '../features/media/pages/MediaLibraryPage'
import { PagesListPage } from '../features/pages/pages/PagesListPage'
import { PageEditPage } from '../features/pages/pages/PageEditPage'
import { MenuEditorPage } from '../features/navigation/pages/MenuEditorPage'
import { NavigationHubPage } from '../features/navigation/pages/NavigationHubPage'
import { RedirectEditPage } from '../features/seo/pages/RedirectEditPage'
import { RedirectsListPage } from '../features/seo/pages/RedirectsListPage'
import { FormEditPage } from '../features/forms/pages/FormEditPage'
import { FormsListPage } from '../features/forms/pages/FormsListPage'
import { SubmissionsListPage } from '../features/forms/pages/SubmissionsListPage'
import { PluginsListPage } from '../features/plugins/pages/PluginsListPage'
import { DashboardPage } from '../pages/DashboardPage'
import { NotFoundPage } from '../pages/NotFoundPage'

export const router = createBrowserRouter([
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    element: <ProtectedLayout />,
    children: [
      { index: true, element: <Navigate to="/dashboard" replace /> },
      { path: 'dashboard', element: <DashboardPage /> },
      { path: 'collections', element: <CollectionsListPage /> },
      { path: 'collections/new', element: <CollectionEditPage /> },
      { path: 'collections/:slug/edit', element: <CollectionEditPage /> },
      { path: 'collections/:slug/fields', element: <CollectionFieldsPage /> },
      { path: 'collections/:slug/entries', element: <CollectionEntriesPage /> },
      { path: 'collections/:slug/entries/new', element: <EntryEditPage /> },
      { path: 'entries/:id/edit', element: <EntryEditPage /> },
      { path: 'entries/:id/preview', element: <EntryPreviewPage /> },
      { path: 'media', element: <MediaLibraryPage /> },
      { path: 'pages', element: <PagesListPage /> },
      { path: 'pages/new', element: <PageEditPage /> },
      { path: 'pages/:slug/edit', element: <PageEditPage /> },
      { path: 'menus', element: <NavigationHubPage /> },
      { path: 'menus/:menuSlug', element: <MenuEditorPage /> },
      { path: 'seo/redirects', element: <RedirectsListPage /> },
      { path: 'seo/redirects/new', element: <RedirectEditPage /> },
      { path: 'seo/redirects/:id/edit', element: <RedirectEditPage /> },
      { path: 'forms', element: <FormsListPage /> },
      { path: 'forms/new', element: <FormEditPage /> },
      { path: 'forms/:slug/edit', element: <FormEditPage /> },
      { path: 'forms/:slug/submissions', element: <SubmissionsListPage /> },
      { path: 'plugins', element: <PluginsListPage /> },
      { path: '*', element: <NotFoundPage /> },
    ],
  },
])
