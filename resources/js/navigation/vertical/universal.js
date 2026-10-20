export default [
  {
    title: 'Dashboard',
    icon: { icon: 'mdi-view-dashboard-outline' },
    href: '/',
  },
  {
    title: 'Knowledge Base',
    icon: { icon: 'mdi-book-open-page-variant-outline' },
    children: [
      {
        title: 'Semua SOP & Dokumen',
        icon: { icon: 'mdi-format-list-bulleted' },
        href: '/articles',
      },
      {
        title: 'Knowledge Spaces',
        icon: { icon: 'mdi-folder-multiple-outline' },
        href: '/spaces',
      },
      {
        title: 'Tulis Dokumen Baru',
        icon: { icon: 'mdi-plus-circle-outline' },
        href: '/articles/create',
      },
      {
        title: 'KMS Analytics',
        icon: { icon: 'mdi-chart-timeline-variant-shimmer' },
        href: '/kms/analytics',
      },
    ],
  },
  {
    title: 'Problem Solving Hub',
    icon: { icon: 'mdi-help-box-multiple-outline' },
    href: '/discussions',
  },
  {
    title: 'Direktori Pakar (SME)',
    icon: { icon: 'mdi-account-star-outline' },
    href: '/experts',
  },
  {
    title: 'My Profile',
    icon: { icon: 'mdi-account-outline' },
    href: '/profile',
  },
]

