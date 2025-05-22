// resources/js/stores/campaignStore.ts
import { defineStore } from 'pinia';
import apiClient from '../services/api';

// Define Campaign interface locally or import from a types file
export interface Campaign {
  id: string;
  title: string;
  description: string;
  goal_amount: number;
  current_amount?: number;
  donations_count?: number;
  start_date: string;
  end_date: string;
  status: string;
  user_id: string;
  user: {
    id: string;
    name: string;
    email: string;
    is_admin: boolean;
};
  created_at: string;
  updated_at: string;
  rejection_reason?: string;
  // Add other fields as needed
}

// Define CampaignFormData interface for create/update
export interface CampaignFormData {
  title: string;
  description: string;
  goal_amount: number | null;
  start_date: string;
  end_date: string;
  // Add other fields if your form collects them
}

export const useCampaignStore = defineStore('campaign', {
    state: () => ({
        userCampaigns: [] as Campaign[],
        userCampaignsLoading: false,
        userCampaignsError: null as string | null,

        publicCampaigns: [] as Campaign[], // For public listing
        publicCampaignsLoading: false,
        publicCampaignsError: null as string | null,

        pendingAdminCampaigns: [] as Campaign[],
        adminCampaignsLoading: false,
        adminCampaignsError: null as string | null,

        currentCampaignForEdit: null as Campaign | null, // For edit form
        fetchCampaignLoading: false, // For fetching single campaign

        createCampaignLoading: false,
        createCampaignError: null as string | null,

        updateCampaignLoading: false,
        updateCampaignError: null as string | null,

        actionLoading: false,
        actionError: null as string | null,
    }),
    actions: {
        // --- Employee Actions ---
        async createCampaign(campaignData: CampaignFormData) {
            this.createCampaignLoading = true;
            this.createCampaignError = null;
            try {
                const response = await apiClient.post<{ data: Campaign }>('/campaigns', campaignData);
                // Add to user's campaigns list locally for immediate feedback
                this.userCampaigns.unshift(response.data.data);
                return response.data.data;
            } catch (error: any) {
                this.createCampaignError = error.response?.data?.message ||
                    (error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : 'Failed to create campaign.');
                console.error("Create campaign error:", error.response?.data);
                throw error;
            } finally {
                this.createCampaignLoading = false;
            }
        },

        // Fetch a single campaign (e.g., for editing)
        async fetchCampaignById(campaignId: string) {
            this.fetchCampaignLoading = true;
            this.currentCampaignForEdit = null;
            this.createCampaignError = null; // Reuse for general campaign fetch errors
            try {
                const response = await apiClient.get<{ data: Campaign }>(`/campaigns/${campaignId}`);
                this.currentCampaignForEdit = response.data.data;
                return response.data.data;
            } catch (error: any) {
                this.createCampaignError = 'Failed to fetch campaign details for editing.';
                console.error("Fetch campaign by ID error:", error.response?.data);
                throw error;
            } finally {
                this.fetchCampaignLoading = false;
            }
        },

        // Update an existing campaign
        async updateCampaign(campaignId: string, campaignData: CampaignFormData) {
            this.updateCampaignLoading = true;
            this.updateCampaignError = null;
            try {
                const response = await apiClient.put<{ data: Campaign }>(`/campaigns/${campaignId}`, campaignData);
                // Update in userCampaigns list
                const index = this.userCampaigns.findIndex(c => c.id === campaignId);
                if (index !== -1) {
                    this.userCampaigns[index] = response.data.data;
                }
                // Update in publicCampaigns list if present
                const publicIndex = this.publicCampaigns.findIndex(c => c.id === campaignId);
                if (publicIndex !== -1) {
                    this.publicCampaigns[publicIndex] = response.data.data;
                }
                this.currentCampaignForEdit = response.data.data; // Update the edited campaign state
                return response.data.data;
            } catch (error: any) {
                this.updateCampaignError = error.response?.data?.message ||
                    (error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : 'Failed to update campaign.');
                console.error("Update campaign error:", error.response?.data);
                throw error;
            } finally {
                this.updateCampaignLoading = false;
            }
        },

        async fetchUserCampaigns() {
            this.userCampaignsLoading = true;
            this.userCampaignsError = null;
            try {
                const response = await apiClient.get<{ data: Campaign[] }>('/my-campaigns');
                this.userCampaigns = response.data.data;
            } catch (error: any) {
                this.userCampaignsError = 'Failed to fetch your campaigns.';
                this.userCampaigns = [];
                console.error("Fetch user campaigns error:", error.response?.data);
            } finally {
                this.userCampaignsLoading = false;
            }
        },

        // Fetch public campaigns with optional search
        async fetchPublicCampaigns(params: { search?: string } = {}) {
            this.publicCampaignsLoading = true;
            this.publicCampaignsError = null;
            try {
                const response = await apiClient.get<{ data: Campaign[] }>('/campaigns', { params });
                this.publicCampaigns = response.data.data;
            } catch (error: any) {
                this.publicCampaignsError = 'Failed to load campaigns.';
                this.publicCampaigns = [];
                console.error("Fetch public campaigns error:", error.response?.data);
            } finally {
                this.publicCampaignsLoading = false;
            }
        },

        // --- Admin Actions ---
        async fetchPendingCampaigns() {
            this.adminCampaignsLoading = true;
            this.adminCampaignsError = null;
            try {
                const response = await apiClient.get<{ data: Campaign[] }>('/admin/campaigns/pending');
                this.pendingAdminCampaigns = response.data.data;
            } catch (error: any) {
                this.adminCampaignsError = 'Failed to fetch pending campaigns.';
                this.pendingAdminCampaigns = [];
                console.error("Fetch pending campaigns error:", error.response?.data);
            } finally {
                this.adminCampaignsLoading = false;
            }
        },

        async approveCampaign(campaignId: string) {
            const updateLocalCampaignStatus = (id: string, newStatus: string) => {
                let campaign = this.userCampaigns.find(c => c.id === id);
                if (campaign) campaign.status = newStatus;

                campaign = this.publicCampaigns.find(c => c.id === id);
                if (campaign) campaign.status = newStatus;

                if (this.currentCampaignForEdit && this.currentCampaignForEdit.id === id) {
                    this.currentCampaignForEdit.status = newStatus;
                }
            };

            this.actionLoading = true;
            this.actionError = null;
            try {
                await apiClient.post(`/admin/campaigns/${campaignId}/approve`);
                this.pendingAdminCampaigns = this.pendingAdminCampaigns.filter(c => c.id !== campaignId);
                updateLocalCampaignStatus(campaignId, 'APPROVED');
            } catch (error: any) {
                this.actionError = error.response?.data?.message || 'Failed to approve campaign.';
                console.error("Approve campaign error:", error.response?.data);
                throw error;
            } finally {
                this.actionLoading = false;
            }
        },

        async rejectCampaign(campaignId: string, reason?: string) {
            const updateLocalCampaignStatus = (id: string, newStatus: string, rejectionReason?: string) => {
                let campaign = this.userCampaigns.find(c => c.id === id);
                if (campaign) {
                    campaign.status = newStatus;
                    if (rejectionReason) campaign.rejection_reason = rejectionReason;
                }

                campaign = this.publicCampaigns.find(c => c.id === id);
                if (campaign) {
                    campaign.status = newStatus;
                    if (rejectionReason) campaign.rejection_reason = rejectionReason;
                }

                if (this.currentCampaignForEdit && this.currentCampaignForEdit.id === id) {
                    this.currentCampaignForEdit.status = newStatus;
                    if (rejectionReason) this.currentCampaignForEdit.rejection_reason = rejectionReason;
                }
            };

            this.actionLoading = true;
            this.actionError = null;
            try {
                await apiClient.post(`/admin/campaigns/${campaignId}/reject`, { reason });
                this.pendingAdminCampaigns = this.pendingAdminCampaigns.filter(c => c.id !== campaignId);
                updateLocalCampaignStatus(campaignId, 'REJECTED', reason);
            } catch (error: any) {
                this.actionError = error.response?.data?.message || 'Failed to reject campaign.';
                console.error("Reject campaign error:", error.response?.data);
                throw error;
            } finally {
                this.actionLoading = false;
            }
        },

        // Helper method to clear errors
        clearErrors() {
            this.createCampaignError = null;
            this.updateCampaignError = null;
            this.userCampaignsError = null;
            this.publicCampaignsError = null;
            this.adminCampaignsError = null;
            this.actionError = null;
        }
    },
});
