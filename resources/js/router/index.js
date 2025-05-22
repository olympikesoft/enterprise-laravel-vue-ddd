// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// Import views - ensure these files exist in ../views/
import HomeView from '../views/HomeView.vue';
import LoginView from '../views/auth/LoginView.vue';
import RegisterView from '../views/auth/RegisterView.vue';
import DashboardView from '../views/DashboardView.vue';

import CreateCampaignView from '../views/campaigns/CreateCampaignView.vue';
import MyCampaignsView from '../views/campaigns/MyCampaignsView.vue';
import CampaignDetailView from '../views/campaigns/CampaignDetailView.vue';
import CampaignListView from '../views/campaigns/CampaignListView.vue';

import AdminDashboard from '../views/admin/AdminDashboard.vue';

import { useAuthStore } from '../stores/auth';

const routes = [
    // Public routes
    {
        path: '/',
        name: 'Home',
        component: HomeView
    },

    // Auth routes (guest only)
    {
        path: '/login',
        name: 'Login',
        component: LoginView,
        meta: { guestOnly: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: RegisterView,
        meta: { guestOnly: true }
    },

    // User dashboard routes
    {
        path: '/dashboard',
        name: 'Dashboard',  // Changed from 'Dashboard' to be more specific
        component: DashboardView,
        meta: { requiresAuth: true }
    },

    // Campaign routes
    {
        path: '/campaigns',
        name: 'CampaignListView',  // Fixed: was 'CampaignListView', should match App.vue
        component: CampaignListView,
        meta: { requiresAuth: true }
    },
    {
        path: '/campaigns/create',
        name: 'CreateCampaign',
        component: CreateCampaignView,
        meta: { requiresAuth: true }
    },
    {
        path: '/campaigns/:id',
        name: 'CampaignDetail',
        component: CampaignDetailView,
        props: true
        // Public route - anyone can view campaign details
    },

    // User-specific routes
    {
        path: '/my-campaigns',
        name: 'MyCampaigns',
        component: MyCampaignsView,
        meta: { requiresAuth: true }
    },
    {
        path: '/my-donations',
        name: 'MyDonations',
        component: MyCampaignsView,  // You'll need to create a proper MyDonationsView
        meta: { requiresAuth: true }
    },

    {
        path: '/admin',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true }
    },

    {
        path: '/admin/campaigns/:id/edit',
        name: 'AdminEditCampaign',
        component: () => import('../views/admin/AdminEditCampaign.vue'),
        meta: { requiresAuth: true, isAdmin: true } // For route guards
    }

    // Fallback route (uncomment when you create NotFoundView)
    // {
    //     path: '/:pathMatch(.*)*',
    //     name: 'NotFound',
    //     component: () => import('../views/NotFoundView.vue')
    // }
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL || '/'),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // If initial auth check isn't done yet, wait for it
    if (!authStore.hasCheckedAuth) {
        await authStore.initializeAuth();
    }

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);
    const guestOnly = to.matched.some(record => record.meta.guestOnly);

    // Handle guest-only routes (login/register)
    if (guestOnly && authStore.isAuthenticated) {
        next({ name: 'UserDashboard' });
        return;
    }

    // Handle auth-required routes
    if (requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'Login', query: { redirect: to.fullPath } });
        return;
    }

    // Handle admin-required routes
    if (requiresAdmin && !authStore.isAdmin) {
        next({ name: 'UserDashboard' }); // Redirect to user dashboard, not admin
        return;
    }

    // Allow navigation
    next();
});

export default router;
