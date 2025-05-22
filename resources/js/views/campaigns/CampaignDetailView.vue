<!-- resources/js/views/campaigns/CampaignDetailView.vue -->
<template>
    <v-container class="py-8">
      <!-- Loading State -->
      <v-row v-if="loading" justify="center">
        <v-col cols="12" class="text-center">
          <v-progress-circular
            :size="50"
            :width="7"
            color="primary"
            indeterminate
          />
          <p class="mt-4">Loading campaign details...</p>
        </v-col>
      </v-row>

      <!-- Error State -->
      <v-row v-else-if="error" justify="center">
        <v-col cols="12" md="8">
          <v-alert type="error" prominent>
            <v-alert-title>Error Loading Campaign</v-alert-title>
            {{ error }}
          </v-alert>
          <div class="text-center mt-4">
            <v-btn color="primary" @click="fetchCampaign" :loading="loading">
              Try Again
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Campaign Content -->
      <div v-else-if="campaign">
        <v-row>
          <!-- Main Campaign Info -->
          <v-col cols="12" lg="8">
            <v-card class="mb-6">

              <v-card-title class="text-h4 pa-6">
                {{ campaign.title }}
              </v-card-title>

              <!-- Campaign Status Badge -->
              <v-card-subtitle class="px-6 pb-0">
                <v-chip
                  :color="getStatusColor(campaign.status)"
                  variant="flat"
                  class="mb-2"
                >
                  {{ formatStatus(campaign.status) }}
                </v-chip>

                <div class="text-body-2 mt-2">
                  Created {{ formatDate(campaign.created_at) }}
                </div>
              </v-card-subtitle>

              <!-- Campaign Description -->
              <v-card-text class="px-6">
                <div class="text-body-1 mb-4">
                  {{ campaign.description }}
                </div>

                <!-- Campaign Dates -->
                <v-row class="mb-4">
                  <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">Start Date</div>
                    <div class="text-body-1">{{ formatDate(campaign.start_date) }}</div>
                  </v-col>
                  <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">End Date</div>
                    <div class="text-body-1">{{ formatDate(campaign.end_date) }}</div>
                  </v-col>
                </v-row>

                <!-- Rejection Reason (if applicable) -->
                <v-alert
                  v-if="campaign.status === 'REJECTED' && campaign.rejection_reason"
                  type="error"
                  variant="tonal"
                  class="mb-4"
                >
                  <v-alert-title>Campaign Rejected</v-alert-title>
                  {{ campaign.rejection_reason }}
                </v-alert>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Donation Sidebar -->
          <v-col cols="12" lg="4">
            <v-card class="sticky-top">
              <!-- Progress Section -->
              <v-card-text>
                <div class="text-h6 mb-4">Fundraising Progress</div>

                <!-- Amount Raised -->
                <div class="text-h4 font-weight-bold primary--text mb-2">
                  ${{ formatAmount(campaign.current_amount || 0) }}
                </div>
                <div class="text-body-2 mb-4">
                  raised of ${{ formatAmount(campaign.goal_amount) }} goal
                </div>

                <!-- Progress Bar -->
                <v-progress-linear
                  :model-value="progressPercentage"
                  height="12"
                  rounded
                  color="primary"
                  class="mb-4"
                />

                <div class="text-body-2 text-center">
                  {{ progressPercentage.toFixed(1) }}% funded
                </div>

                <!-- Days Remaining -->
                <div v-if="daysRemaining !== null" class="text-center mt-4">
                  <div class="text-h6">{{ daysRemaining }}</div>
                  <div class="text-body-2">{{ daysRemaining === 1 ? 'day' : 'days' }} remaining</div>
                </div>
              </v-card-text>

              <v-divider />

              <!-- Donation Form -->
              <v-card-text v-if="canDonate">
                <div class="text-h6 mb-4">Make a Donation</div>

                <v-form @submit.prevent="submitDonation">
                  <v-text-field
                    v-model.number="donationAmount"
                    label="Donation Amount"
                    type="number"
                    prefix="$"
                    :min="1"
                    :rules="[rules.required, rules.minAmount]"
                    :disabled="donationLoading"
                    variant="outlined"
                    class="mb-4"
                  />

                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="donationLoading"
                    :disabled="!donationAmount || donationAmount < 1"
                    block
                    size="large"
                  >
                    Donate Now
                  </v-btn>
                </v-form>

                <!-- Donation Error -->
                <v-alert
                  v-if="donationError"
                  type="error"
                  class="mt-4"
                  dismissible
                  @click:close="donationError = ''"
                >
                  {{ donationError }}
                </v-alert>

                <!-- Donation Success -->
                <v-alert
                  v-if="donationSuccess"
                  type="success"
                  class="mt-4"
                  dismissible
                  @click:close="donationSuccess = ''"
                >
                  Thank you for your donation!
                </v-alert>
              </v-card-text>

              <!-- Campaign Owner Actions -->
              <v-card-actions v-if="isOwner" class="pa-4">
                <v-btn
                  color="primary"
                  variant="outlined"
                  :to="{ name: 'EditCampaign', params: { id: campaign.id } }"
                  block
                >
                  <v-icon start>mdi-pencil</v-icon>
                  Edit Campaign
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>

        <!-- Campaign Updates Section -->
        <v-row class="mt-6">
          <v-col cols="12">
            <v-card>
              <v-card-title>
                <v-icon start>mdi-bullhorn</v-icon>
                Campaign Updates
              </v-card-title>
              <v-card-text>
                <div class="text-body-2 text-medium-emphasis">
                  No updates available yet.
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </div>
    </v-container>
  </template>

  <script setup lang="ts">
  import { ref, computed, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import { useCampaignStore, type Campaign } from '../../stores/campaign';
  import { useAuthStore } from '../../stores/auth';
  import apiClient from '../../services/api';

  const route = useRoute();
  const campaignStore = useCampaignStore();
  const authStore = useAuthStore();

  // Component state
  const campaign = ref<Campaign | null>(null);
  const loading = ref(true);
  const error = ref('');
  const donationAmount = ref<number | null>(null);
  const donationLoading = ref(false);
  const donationError = ref('');
  const donationSuccess = ref('');

  // Validation rules
  const rules = {
    required: (value: any) => !!value || 'Amount is required',
    minAmount: (value: number) => value >= 1 || 'Minimum donation is $1'
  };

  // Computed properties
  const progressPercentage = computed(() => {
    if (!campaign.value) return 0;
    return Math.min((campaign.value.current_amount || 0) / campaign.value.goal_amount * 100, 100);
  });

  const daysRemaining = computed(() => {
    if (!campaign.value) return null;
    const endDate = new Date(campaign.value.end_date);
    const today = new Date();
    const diffTime = endDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
  });

  const canDonate = computed(() => {
    return campaign.value &&
           campaign.value.status === 'APPROVED' &&
           daysRemaining.value !== null &&
           daysRemaining.value > 0 &&
           authStore.isAuthenticated &&
           !isOwner.value;
  });

  const isOwner = computed(() => {
    return campaign.value &&
           authStore.user &&
           campaign.value.user_id === authStore.user.id.toString();
  });

  // Methods
  const fetchCampaign = async () => {
    const campaignId = route.params.id as string;
    loading.value = true;
    error.value = '';

    try {
      const response = await apiClient.get<{ data: Campaign }>(`/campaigns/${campaignId}`);
      campaign.value = response.data.data;
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to load campaign';
      console.error('Fetch campaign error:', err);
    } finally {
      loading.value = false;
    }
  };

  const submitDonation = async () => {
    if (!donationAmount.value || !campaign.value) return;

    donationLoading.value = true;
    donationError.value = '';
    donationSuccess.value = '';

    try {
      await apiClient.post('/donations', {
        campaign_id: campaign.value.id,
        amount: donationAmount.value
      });

      donationSuccess.value = 'Thank you for your donation!';
      donationAmount.value = null;

      // Refresh campaign to update current amount
      await fetchCampaign();
    } catch (err: any) {
      donationError.value = err.response?.data?.message || 'Failed to process donation';
      console.error('Donation error:', err);
    } finally {
      donationLoading.value = false;
    }
  };

  const formatAmount = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
      'PENDING': 'orange',
      'APPROVED': 'green',
      'REJECTED': 'red',
      'DRAFT': 'grey'
    };
    return colors[status] || 'grey';
  };

  const formatStatus = (status: string): string => {
    return status.charAt(0) + status.slice(1).toLowerCase();
  };

  onMounted(() => {
    fetchCampaign();
  });
  </script>

  <style scoped>
  .sticky-top {
    position: sticky;
    top: 20px;
  }
  </style>
