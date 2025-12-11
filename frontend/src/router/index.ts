import { createRouter, createWebHistory } from 'vue-router';

import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: () => import('@/views/HomeView.vue'),
            meta: { requiresAuth: false },
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/auth/LoginView.vue'),
            meta: { requiresAuth: false, guestOnly: true },
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('@/views/auth/RegisterView.vue'),
            meta: { requiresAuth: false, guestOnly: true },
        },
        {
            path: '/sandbox',
            name: 'sandbox',
            component: () => import('@/views/SandboxView.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/exercises',
            name: 'exercises',
            component: () => import('@/views/ExercisesView.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/exercises/:id',
            name: 'exercise',
            component: () => import('@/views/ExerciseDetailView.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'not-found',
            component: () => import('@/views/NotFoundView.vue'),
        },
    ],
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;

    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'login', query: { redirect: to.fullPath } });
    } else if (to.meta.guestOnly && isAuthenticated) {
        next({ name: 'home' });
    } else {
        next();
    }
});

export default router;
