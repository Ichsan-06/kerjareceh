export const routes = [
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: '',
        component: () => import('@/pages/LandingPage.vue'),
      },
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
      },
      {
        path: 'reset-password',
        component: () => import('@/pages/reset-password.vue'),
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'dashboard',
        component: () => import('@/pages/dashboard.vue'),
      },
      {
        path: 'users',
        component: () => import('@/pages/user/index.vue'),
      },
      {
        path: 'users/add',
        component: () => import('@/pages/user/add.vue'),
      },
      {
        path: 'users/:id/edit',
        component: () => import('@/pages/user/edit.vue'),
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
      },
      {
        path: 'roles',
        component: () => import('@/pages/roles/index.vue'),
      },
      {
        path: 'jobs',
        component: () => import('@/pages/jobs/index.vue'),
      },
      {
        path: 'jobs/add',
        component: () => import('@/pages/jobs/add.vue'),
      },
      {
        path: 'jobs/:id/edit',
        component: () => import('@/pages/jobs/edit.vue'),
      },
      {
        path: 'jobs/:id',
        component: () => import('@/pages/jobs/view.vue'),
      },
      {
        path: 'my-jobs',
        component: () => import('@/pages/my-jobs/index.vue'),
      },
      {
        path: 'posted-jobs',
        component: () => import('@/pages/posted-jobs/index.vue'),
      },
      {
        path: 'wallet',
        component: () => import('@/pages/wallet/index.vue'),
      },
      {
        path: 'leaderboard',
        component: () => import('@/pages/leaderboard/index.vue'),
      },
      {
        path: 'topup',
        component: () => import('@/pages/topup/index.vue'),
      },
      {
        path: 'admin/topup',
        component: () => import('@/pages/admin/topup/index.vue'),
      },
      {
        path: 'feedback',
        component: () => import('@/pages/feedback/index.vue'),
      },
      {
        path: 'withdraw',
        component: () => import('@/pages/withdraw/index.vue'),
      },
      {
        path: 'admin/withdraw',
        component: () => import('@/pages/admin/withdraw/index.vue'),
      },
      {
        path: 'traktir',
        component: () => import('@/pages/traktir.vue'),
      },
      {
        path: 'typography',
        component: () => import('@/pages/typography.vue'),
      },
      {
        path: 'icons',
        component: () => import('@/pages/icons.vue'),
      },
      {
        path: 'cards',
        component: () => import('@/pages/cards.vue'),
      },
      {
        path: 'tables',
        component: () => import('@/pages/tables.vue'),
      },
      {
        path: 'form-layouts',
        component: () => import('@/pages/form-layouts.vue'),
      },
    ],
  },
]
