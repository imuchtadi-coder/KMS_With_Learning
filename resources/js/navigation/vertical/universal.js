export default [
  {
    title: 'Dashboard',
    icon: { icon: 'mdi-view-dashboard-outline' },
    href: '/',
  },
  {
    title: 'My Profile',
    icon: { icon: 'mdi-account-outline' },
    href: '/profile',
  },
  {
    title: 'Discussions',
    icon: { icon: 'mdi-forum-outline' },
    href: '/discussions',
  },
  {
    title: 'Knowledge Base',
    icon: { icon: 'mdi-book-open-page-variant-outline' },
    children: [
      {
        title: 'Semua Artikel & SOP',
        icon: { icon: 'mdi-format-list-bulleted' },
        href: '/articles',
      },
      {
        title: 'Tulis Artikel / SOP Baru',
        icon: { icon: 'mdi-plus-circle-outline' },
        href: '/articles/create',
      },
    ],
  },
]
