// resources/js/stores/donationStore.ts
import { defineStore } from 'pinia';
import apiClient from '@/services/api';

// Define interfaces
export interface DonationDataPayload {
  campaign_id: string;
  amount: number; // Ensure amount is number
  message?: string;
  payment_token: string;
}

export interface CreatedDonation {
    id: string;
    campaign_id: string;
    amount: number;
    status: string;
    message?: string;
    donated_at: string; // From backend
}

export interface UserDonation extends CreatedDonation { // For listing user's donations
    campaign_title?: string; // Helpful for display
}


export const useDonationStore = defineStore('donation', {
    state: () => ({
        donationLoading: false,
        donationError: null as string | null,
        lastDonation: null as CreatedDonation | null,

        myDonations: [] as UserDonation[], // NEW
        myDonationsLoading: false, // NEW
        myDonationsError: null as string | null, // NEW
    }),
    actions: {
        async makeDonation(donationData: DonationDataPayload): Promise<CreatedDonation> {
            this.donationLoading = true;
            this.donationError = null;
            this.lastDonation = null;

            if (!donationData.amount || donationData.amount <= 0) {
                this.donationError = "Donation amount must be greater than zero.";
                this.donationLoading = false;
                throw new Error(this.donationError);
            }
            if (!donationData.payment_token) {
                this.donationError = "Payment information is missing.";
                this.donationLoading = false;
                throw new Error(this.donationError);
            }

            try {
                const response = await apiClient.post<{ data: CreatedDonation }>('/donations', donationData);
                this.lastDonation = response.data.data;
                // Optionally add to a local list of user's donations if desired for immediate feedback
                // this.myDonations.unshift({...response.data.data, campaign_title: 'Known from context or fetch'});
                return response.data.data;
            } catch (error: any) {
                let errorMessage = 'Donation failed. Please try again.';
                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response?.data?.errors) {
                    errorMessage = Object.values(error.response.data.errors).flat().join(' ');
                }
                this.donationError = errorMessage;
                console.error("Make donation error:", error.response?.data);
                throw error;
            } finally {
                this.donationLoading = false;
            }
        },

        // NEW: Fetch user's donation history
        async fetchMyDonations() {
            this.myDonationsLoading = true;
            this.myDonationsError = null;
            try {
                const response = await apiClient.get<{data: UserDonation[]}>('/my-donations'); // Assumed API endpoint
                this.myDonations = response.data.data;
            } catch (error: any) {
                this.myDonationsError = "Failed to fetch your donation history.";
                this.myDonations = [];
                console.error("Fetch my donations error:", error.response?.data);
            } finally {
                this.myDonationsLoading = false;
            }
        },

        clearError() {
            this.donationError = null;
        }
    },
});
