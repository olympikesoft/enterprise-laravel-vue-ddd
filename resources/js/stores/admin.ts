// resources/js/stores/adminStore.ts
import { defineStore } from 'pinia';
import apiClient from '@/services/api';

interface DashboardStats {
    pendingCampaignsCount: number;
    activeCampaignsCount: number;
    totalDonationsAmount: number;
    // Add more stats as needed
}

export const useAdminStore = defineStore('admin', {
    state: () => ({
        dashboardStats: null as DashboardStats | null,
        dashboardLoading: false,
        dashboardError: null as string | null,
    }),
    actions: {
        async fetchDashboardStats() {
            this.dashboardLoading = true;
            this.dashboardError = null;
            try {
                const response = await apiClient.get<{ data: DashboardStats }>('/admin/dashboard-stats'); // Assumed API endpoint
                this.dashboardStats = response.data.data;
            } catch (error: any) {
                this.dashboardError = "Failed to load dashboard statistics.";
                this.dashboardStats = null;
                console.error("Fetch dashboard stats error:", error.response?.data);
            } finally {
                this.dashboardLoading = false;
            }
        }
    }
});
