<!-- resources/js/views/UserDashboard.vue -->
<template>
    <v-container class="py-8">
      <!-- Welcome Header -->
      <v-row class="mb-6">
        <v-col cols="12">
          <div class="d-flex align-center mb-4">
            <v-avatar
              color="primary"
              size="60"
              class="mr-4"
            >
              <v-icon size="30">mdi-account</v-icon>
            </v-avatar>
            <div>
              <h1 class="text-h4 font-weight-bold">
                Welcome back, {{ authStore.user?.name || 'User' }}!
              </h1>
              <p class="text-body-1 text-medium-emphasis ma-0">
                Manage your campaigns and track your fundraising progress
              </p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Quick Actions -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card class="pa-4">
            <v-card-title class="pb-4">
              <v-icon start>mdi-lightning-bolt</v-icon>
              Quick Actions
            </v-card-title>
            <v-row>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  :to="{ name: 'CreateCampaign' }"
                  color="primary"
                  variant="elevated"
                  size="large"
                  block
                  class="py-4"
                >
                  <v-icon start>mdi-plus</v-icon>
                  Create Campaign
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  :to="{ name: 'MyCampaigns' }"
                  color="info"
                  variant="elevated"
                  size="large"
                  block
                  class="py-4"
                >
                  <v-icon start>mdi-view-list</v-icon>
                  My Campaigns
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  :to="{ name: 'MyDonations' }"
                  color="success"
                  variant="elevated"
                  size="large"
                  block
                  class="py-4"
                >
                  <v-icon start>mdi-heart</v-icon>
                  My Donations
                </v-btn>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>

      <!-- Dashboard Stats -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" md="3">
          <v-card class="text-center pa-4" color="primary" variant="tonal">
            <v-icon size="40" class="mb-2">mdi-bullhorn</v-icon>
            <div class="text-h4 font-weight-bold">{{ campaignStats.total }}</div>
            <div class="text-body-2">Total Campaigns</div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card class="text-center pa-4" color="success" variant="tonal">
            <v-icon size="40" class="mb-2">mdi-check-circle</v-icon>
            <div class="text-h4 font-weight-bold">{{ campaignStats.approved }}</div>
            <div class="text-body-2">Active Campaigns</div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card class="text-center pa-4" color="info" variant="tonal">
            <v-icon size="40" class="mb-2">mdi-currency-usd</v-icon>
            <div class="text-h4 font-weight-bold">${{ formatAmount(campaignStats.totalRaised) }}</div>
            <div class="text-body-2">Total Raised</div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card class="text-center pa-4" color="warning" variant="tonal">
            <v-icon size="40" class="mb-2">mdi-clock-outline</v-icon>
            <div class="text-h4 font-weight-bold">{{ campaignStats.pending }}</div>
            <div class="text-body-2">Pending Approval</div>
          </v-card>
        </v-col>
      </v-row>

      <!-- Recent Campaigns -->
      <v-row>
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="d-flex align-center justify-space-between">
              <div>
                <v-icon start>mdi-bullhorn</v-icon>
                Recent Campaigns
              </div>
              <v-btn
                :to="{ name: 'MyCampaigns' }"
                variant="text"
                color="primary"
              >
                View All
              </v-btn>
            </v-card-title>

            <v-divider />

            <!-- Loading State -->
            <div v-if="campaignStore.userCampaignsLoading" class="text-center py-8">
              <v-progress-circular indeterminate color="primary" />
              <p class="mt-4">Loading your campaigns...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="recentCampaigns.length === 0" class="text-center py-8">
              <v-icon size="60" class="mb-4 text-medium-emphasis">mdi-bullhorn-outline</v-icon>
              <h3 class="text-h6 mb-2">No campaigns yet</h3>
              <p class="text-body-2 text-medium-emphasis mb-4">
                Start your fundraising journey by creating your first campaign
              </p>
              <v-btn
                :to="{ name: 'CreateCampaign' }"
                color="primary"
                variant="elevated"
              >
                <v-icon start>mdi-plus</v-icon>
                Create Your First Campaign
              </v-btn>
            </div>

            <!-- Campaigns List -->
            <div v-else>
              <v-list>
                <v-list-item
                  v-for="campaign in recentCampaigns"
                  :key="campaign.id"
                  :to="{ name: 'CampaignDetail', params: { id: campaign.id } }"
                  class="px-4 py-3"
                >
                  <template v-slot:prepend>
                    <v-avatar color="primary" variant="tonal">
                      <v-icon>mdi-bullhorn</v-icon>
                    </v-avatar>
                  </template>

                  <v-list-item-title class="font-weight-medium">
                    {{ campaign.title }}
                  </v-list-item-title>

                  <v-list-item-subtitle>
                    <div class="d-flex align-center mt-1">
                      <v-chip
                        :color="getStatusColor(campaign.status)"
                        size="small"
                        variant="flat"
                        class="mr-2"
                      >
                        {{ formatStatus(campaign.status) }}
                      </v-chip>
                      <span class="text-body-2">
                        ${{ formatAmount(campaign.current_amount || 0) }} of ${{ formatAmount(campaign.goal_amount) }}
                      </span>
                    </div>
                  </v-list-item-subtitle>

                  <template v-slot:append>
                    <div class="text-right">
                      <div class="text-body-2 text-medium-emphasis">
                        {{ formatDate(campaign.created_at) }}
                      </div>
                      <v-progress-linear
                        :model-value="getProgressPercentage(campaign)"
                        height="4"
                        rounded
                        color="primary"
                        class="mt-1"
                        style="width: 100px;"
                      />
                    </div>
                  </template>
                </v-list-item>
              </v-list>
            </div>
          </v-card>
        </v-col>

        <!-- Recent Activity Sidebar -->
        <v-col cols="12" lg="4">
          <v-card>
            <v-card-title>
              <v-icon start>mdi-history</v-icon>
              Recent Activity
            </v-card-title>

            <v-divider />

            <div class="text-center py-8">
              <v-icon size="60" class="mb-4 text-medium-emphasis">mdi-clock-outline</v-icon>
              <h3 class="text-h6 mb-2">No recent activity</h3>
              <p class="text-body-2 text-medium-emphasis">
                Your recent donations and campaign updates will appear here
              </p>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>

  <script setup lang="ts">
  import { computed, onMounted } from 'vue';
  import { useAuthStore } from '../stores/auth';
  import { useCampaignStore, type Campaign } from '../stores/campaign';

  const authStore = useAuthStore();
  const campaignStore = useCampaignStore();

  // Computed properties
  const recentCampaigns = computed(() => {
    return campaignStore.userCampaigns.slice(0, 5); // Show only 5 most recent
  });

  const campaignStats = computed(() => {
    const campaigns = campaignStore.userCampaigns;
    return {
      total: campaigns.length,
      approved: campaigns.filter(c => c.status === 'APPROVED').length,
      pending: campaigns.filter(c => c.status === 'PENDING').length,
      totalRaised: campaigns.reduce((sum, c) => sum + (c.current_amount || 0), 0)
    };
  });

  // Methods
  const formatAmount = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  };

  const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
      'PENDING': 'orange',
      'APPROVED': 'success',
      'REJECTED': 'error',
      'DRAFT': 'grey'
    };
    return colors[status] || 'grey';
  };

  const formatStatus = (status: string): string => {
    return status.charAt(0) + status.slice(1).toLowerCase();
  };

  const getProgressPercentage = (campaign: Campaign): number => {
    return Math.min((campaign.current_amount || 0) / campaign.goal_amount * 100, 100);
  };

  // Lifecycle
  onMounted(() => {
    // Fetch user's campaigns when component mounts
    campaignStore.fetchUserCampaigns();
  });
  </script>
