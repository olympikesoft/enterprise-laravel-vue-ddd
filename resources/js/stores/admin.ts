// resources/js/stores/adminStore.ts
import { defineStore } from 'pinia';
import apiClient from '../services/api';

interface DashboardStats {
    pendingCampaignsCount: number;
    activeCampaignsCount: number;
    totalDonationsAmount: number;
    // Add more stats as needed
}

interface Campaign {
    id: string | number | null;
    title: string;
    description: string;
    goal_amount: number;
    current_amount?: number;
    end_date: string;
    status: 'PENDING' | 'APPROVED' | 'REJECTED' | 'DRAFT' | 'COMPLETED' | string;
    category_id?: number | null;
    user?: {
        id: number;
        name: string;
    };
    rejection_reason?: string;
    created_at?: string;
    updated_at?: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at?: string;
    updated_at?: string;
}

export const useAdminStore = defineStore('admin', {
    state: () => ({
        dashboardStats: null as DashboardStats | null,
        dashboardLoading: false,
        dashboardError: null as string | null,

        campaigns: [] as Campaign[],
        campaignsLoading: false,
        campaignsError: null as string | null,

        users: [] as User[],
        usersLoading: false,
        usersError: null as string | null,
    }),

    actions: {
        // Dashboard Stats
        async fetchDashboardStats() {
            this.dashboardLoading = true;
            this.dashboardError = null;
            try {
                const response = await apiClient.get<{ data: DashboardStats }>('/admin/dashboard-stats');
                this.dashboardStats = response.data.data;
            } catch (error: any) {
                this.dashboardError = "Failed to load dashboard statistics.";
                this.dashboardStats = null;
                console.error("Fetch dashboard stats error:", error.response?.data);
            } finally {
                this.dashboardLoading = false;
            }
        },

        // Campaign Management
        async fetchCampaigns() {
            this.campaignsLoading = true;
            this.campaignsError = null;
            try {
                const response = await apiClient.get<{ data: Campaign[] }>('/admin/campaigns');
                this.campaigns = response.data.data;
            } catch (error: any) {
                this.campaignsError = "Failed to load campaigns.";
                this.campaigns = [];
                console.error("Fetch campaigns error:", error.response?.data);
            } finally {
                this.campaignsLoading = false;
            }
        },

        async fetchPendingCampaigns() {
            this.campaignsLoading = true;
            this.campaignsError = null;
            try {
                const response = await apiClient.get<{ data: Campaign[] }>('/admin/campaigns/pending');
                this.campaigns = response.data.data;
            } catch (error: any) {
                this.campaignsError = "Failed to load pending campaigns.";
                this.campaigns = [];
                console.error("Fetch pending campaigns error:", error.response?.data);
            } finally {
                this.campaignsLoading = false;
            }
        },

        async fetchCampaign(id: string | number): Promise<Campaign | null> {
            try {
                const response = await apiClient.get<{ data: Campaign }>(`/admin/campaigns/${id}`);
                return response.data.data;
            } catch (error: any) {
                console.error("Fetch campaign error:", error.response?.data);
                throw error;
            }
        },

        async updateCampaign(id: string | number | null, data: Partial<Campaign>): Promise<Campaign> {
            try {
                const response = await apiClient.put<{ data: Campaign }>(`/admin/campaigns/${id}`, data);

                // Update the campaign in the local state if it exists
                const index = this.campaigns.findIndex(campaign => campaign.id == id);
                if (index !== -1) {
                    this.campaigns[index] = response.data.data;
                }

                return response.data.data;
            } catch (error: any) {
                console.error("Update campaign error:", error.response?.data);
                throw error;
            }
        },

        async approveCampaign(id: string | number): Promise<Campaign> {
            try {
                const response = await apiClient.post<{ data: Campaign }>(`/admin/campaigns/${id}/approve`);

                // Update the campaign in the local state
                const index = this.campaigns.findIndex(campaign => campaign.id == id);
                if (index !== -1) {
                    this.campaigns[index] = response.data.data;
                }

                return response.data.data;
            } catch (error: any) {
                console.error("Approve campaign error:", error.response?.data);
                throw error;
            }
        },

        async rejectCampaign(id: string | number, rejectionReason?: string): Promise<Campaign> {
            try {
                const response = await apiClient.post<{ data: Campaign }>(`/admin/campaigns/${id}/reject`, {
                    rejection_reason: rejectionReason
                });

                // Update the campaign in the local state
                const index = this.campaigns.findIndex(campaign => campaign.id == id);
                if (index !== -1) {
                    this.campaigns[index] = response.data.data;
                }

                return response.data.data;
            } catch (error: any) {
                console.error("Reject campaign error:", error.response?.data);
                throw error;
            }
        },

        // User Management
        async fetchUsers() {
            this.usersLoading = true;
            this.usersError = null;
            try {
                const response = await apiClient.get<{ data: User[] }>('/admin/users');
                this.users = response.data.data;
            } catch (error: any) {
                this.usersError = "Failed to load users.";
                this.users = [];
                console.error("Fetch users error:", error.response?.data);
            } finally {
                this.usersLoading = false;
            }
        },

        async fetchUser(id: string | number): Promise<User | null> {
            try {
                const response = await apiClient.get<{ data: User }>(`/admin/users/${id}`);
                return response.data.data;
            } catch (error: any) {
                console.error("Fetch user error:", error.response?.data);
                throw error;
            }
        },

        async updateUser(id: string | number, data: Partial<User>): Promise<User> {
            try {
                const response = await apiClient.put<{ data: User }>(`/admin/users/${id}`, data);

                // Update the user in the local state if it exists
                const index = this.users.findIndex(user => user.id == id);
                if (index !== -1) {
                    this.users[index] = response.data.data;
                }

                return response.data.data;
            } catch (error: any) {
                console.error("Update user error:", error.response?.data);
                throw error;
            }
        },

        async deleteUser(id: string | number): Promise<void> {
            try {
                await apiClient.delete(`/admin/users/${id}`);

                // Remove the user from the local state
                this.users = this.users.filter(user => user.id != id);
            } catch (error: any) {
                console.error("Delete user error:", error.response?.data);
                throw error;
            }
        }
    }
});
