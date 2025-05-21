// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// Import views - ensure these files exist in ../views/
import HomeView from '../views/HomeView.vue';
import LoginView from '../views/auth/LoginView.vue'; // Organized auth views
import RegisterView from '../views/auth/RegisterView.vue';
import DashboardView from '../views/DashboardView.vue';

import CreateCampaignView from '../views/campaigns/CreateCampaignView.vue';
import MyCampaignsView from '../views/campaigns/MyCampaignsView.vue';

import AdminDashboardLayout from '../views/admin/AdminDashboardLayout.vue'; // Admin layout
import AdminPendingCampaignsView from '../views/admin/AdminPendingCampaignsView.vue';
// import NotFoundView from '../views/NotFoundView.vue'; // Optional

import { useAuthStore } from '../stores/auth';

const routes = [
    { path: '/', name: 'Home', component: HomeView },
    { path: '/login', name: 'Login', component: LoginView, meta: { guestOnly: true } },
    { path: '/register', name: 'Register', component: RegisterView, meta: { guestOnly: true } },

    { path: '/dashboard', name: 'Dashboard', component: DashboardView, meta: { requiresAuth: true } },
    { path: '/campaigns/create', name: 'CreateCampaign', component: CreateCampaignView, meta: { requiresAuth: true } },
    { path: '/my-campaigns', name: 'MyCampaigns', component: MyCampaignsView, meta: { requiresAuth: true } },

    {
        path: '/admin',
        component: AdminDashboardLayout, // Use a layout for admin section
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            { path: '', name: 'AdminDashboard', redirect: { name: 'AdminPendingCampaigns' } }, // Default admin page
            {
                path: 'campaigns/pending',
                name: 'AdminPendingCampaigns',
                component: AdminPendingCampaignsView,
            },
            // Add other admin child routes here
        ]
    },
    // { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFoundView }
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL || '/'), // Ensure BASE_URL is set in .env if needed
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // If initial auth check isn't done yet, wait for it.
    // This is important if navigating directly to a protected route.
    if (!authStore.hasCheckedAuth) {
        await authStore.initializeAuth();
    }

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);
    const guestOnly = to.matched.some(record => record.meta.guestOnly);

    if (guestOnly && authStore.isAuthenticated) {
        next({ name: 'Dashboard' });
    } else if (requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'Login', query: { redirect: to.fullPath } });
    } else if (requiresAdmin && !authStore.isAdmin) {
        next({ name: 'Dashboard', query: { unauthorized: 'true' } }); // Or a dedicated 'UnauthorizedView'
    } else {
        next();
    }
});

export default router;
