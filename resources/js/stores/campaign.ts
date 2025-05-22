// resources/js/stores/campaignStore.ts
import { defineStore } from 'pinia';
import apiClient from '@/services/api';
import type { Campaign } from '@/views/campaigns/CampaignDetailView.vue'; // Assuming Campaign interface is here

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

        updateCampaignLoading: false, // NEW
        updateCampaignError: null as string | null, // NEW

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
                this.createCampaignError = error.response?.data?.message || error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : 'Failed to create campaign.';
                console.error("Create campaign error:", error.response?.data);
                throw error;
            } finally {
                this.createCampaignLoading = false;
            }
        },

        // NEW: Fetch a single campaign (e.g., for editing)
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

        // NEW: Update an existing campaign
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
                this.updateCampaignError = error.response?.data?.message || error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : 'Failed to update campaign.';
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

        // MODIFIED: Fetch public campaigns with optional search
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


        // --- Admin Actions (existing, ensure they use Campaign interface) ---
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
            // ... (existing implementation)
            // On success, also update status in userCampaigns and publicCampaigns if the campaign exists there
            const updateLocalCampaignStatus = (id: string, newStatus: string) => {
                let campaign = this.userCampaigns.find(c => c.id === id);
                if (campaign) campaign.status = newStatus;
                campaign = this.publicCampaigns.find(c => c.id === id);
                if (campaign) campaign.status = newStatus;
                // also for currentCampaignForEdit if it's the one being approved/rejected
                if(this.currentCampaignForEdit && this.currentCampaignForEdit.id === id) {
                    this.currentCampaignForEdit.status = newStatus;
                }
            };

            this.actionLoading = true;
            this.actionError = null;
            try {
                await apiClient.post(`/admin/campaigns/${campaignId}/approve`);
                this.pendingAdminCampaigns = this.pendingAdminCampaigns.filter(c => c.id !== campaignId);
                updateLocalCampaignStatus(campaignId, 'APPROVED'); // Or whatever status backend sets
            } catch (error: any) {
                this.actionError = error.response?.data?.message || 'Failed to approve campaign.';
                console.error("Approve campaign error:", error.response?.data);
                throw error;
            } finally {
                this.actionLoading = false;
            }
        },
        async rejectCampaign(campaignId: string, reason?: string) {
            const updateLocalCampaignStatus = (id: string, newStatus: string) => {
                let campaign = this.userCampaigns.find(c => c.id === id);
                if (campaign) {
                    campaign.status = newStatus;
                    // @ts-ignore because rejection_reason might not be on Campaign interface yet
                    if (rejectionReason) campaign.rejection_reason = rejectionReason;
                }
                campaign = this.publicCampaigns.find(c => c.id === id);
                 if (campaign) {
                    campaign.status = newStatus;
                    // @ts-ignore
                    if (rejectionReason) campaign.rejection_reason = rejectionReason;
                }
                if(this.currentCampaignForEdit && this.currentCampaignForEdit.id === id) {
                    this.currentCampaignForEdit.status = newStatus;
                    // @ts-ignore
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
    },
});
