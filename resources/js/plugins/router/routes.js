export const routes = [
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: '',
        name: 'summarizer',
        component: () => import('@/pages/Summarizer'),
        meta: { title: 'Resumir documento' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: '',
        name: 'not-found',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
]
