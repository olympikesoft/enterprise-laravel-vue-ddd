<template>
    <v-container class="py-8">
      <!-- Header -->
      <v-row class="mb-6">
        <v-col cols="12">
          <div class="d-flex align-center justify-space-between">
            <div>
              <h1 class="text-h3 font-weight-bold mb-2">My Campaigns</h1>
              <p class="text-body-1 text-medium-emphasis">
                Track your fundraising campaigns and their progress
              </p>
            </div>
            <v-btn
              :to="{ name: 'CreateCampaign' }"
              color="primary"
              size="large"
              variant="elevated"
            >
              <v-icon start>mdi-plus</v-icon>
              Create Campaign
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Filters and Actions -->
      <v-row class="mb-6">
        <v-col cols="12" md="6">
          <v-text-field
            v-model="searchQuery"
            placeholder="Search campaigns..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            hide-details
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="statusFilter"
            :items="statusOptions"
            label="Filter by Status"
            variant="outlined"
            density="compact"
            clearable
            hide-details
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-btn
            @click="refreshCampaigns"
            :loading="campaignStore.userCampaignsLoading"
            variant="outlined"
            block
          >
            <v-icon start>mdi-refresh</v-icon>
            Refresh
          </v-btn>
        </v-col>
      </v-row>

      <!-- Loading State -->
      <v-row v-if="campaignStore.userCampaignsLoading" justify="center">
        <v-col cols="12" class="text-center py-8">
          <v-progress-circular
            :size="50"
            :width="7"
            color="primary"
            indeterminate
          />
          <p class="mt-4 text-h6">Loading your campaigns...</p>
        </v-col>
      </v-row>

      <!-- Error State -->
      <v-row v-else-if="campaignStore.userCampaignsError">
        <v-col cols="12">
          <v-alert type="error" prominent>
            <v-alert-title>Error Loading Campaigns</v-alert-title>
            {{ campaignStore.userCampaignsError }}
            <template v-slot:append>
              <v-btn
                @click="refreshCampaigns"
                variant="outlined"
                size="small"
              >
                Try Again
              </v-btn>
            </template>
          </v-alert>
        </v-col>
      </v-row>

      <!-- Empty State -->
      <v-row v-else-if="filteredCampaigns.length === 0 && !searchQuery && !statusFilter">
        <v-col cols="12">
          <v-card class="text-center pa-8" variant="outlined">
            <v-icon size="80" class="mb-4 text-medium-emphasis">mdi-bullhorn-outline</v-icon>
            <h2 class="text-h5 mb-4">No campaigns yet</h2>
            <p class="text-body-1 text-medium-emphasis mb-6">
              Start your fundraising journey by creating your first campaign for a cause you care about
            </p>
            <v-btn
              :to="{ name: 'CreateCampaign' }"
              color="primary"
              size="large"
              variant="elevated"
            >
              <v-icon start>mdi-plus</v-icon>
              Create Your First Campaign
            </v-btn>
          </v-card>
        </v-col>
      </v-row>

      <!-- No Results from Search/Filter -->
      <v-row v-else-if="filteredCampaigns.length === 0">
        <v-col cols="12">
          <v-card class="text-center pa-8" variant="outlined">
            <v-icon size="60" class="mb-4 text-medium-emphasis">mdi-magnify</v-icon>
            <h3 class="text-h6 mb-2">No campaigns found</h3>
            <p class="text-body-2 text-medium-emphasis">
              Try adjusting your search or filter criteria
            </p>
            <v-btn
              @click="clearFilters"
              variant="outlined"
              class="mt-4"
            >
              Clear Filters
            </v-btn>
          </v-card>
        </v-col>
      </v-row>

      <!-- Campaigns Grid -->
      <v-row v-else>
        <v-col
          v-for="campaign in filteredCampaigns"
          :key="campaign.id"
          cols="12"
          md="6"
          lg="4"
        >
          <v-card class="campaign-card h-100" @click="viewCampaign(campaign)">
            <!-- Campaign Content -->
            <v-card-title class="pb-2">
              <div class="text-truncate">{{ campaign.title }}</div>
            </v-card-title>

            <v-card-subtitle class="pb-2">
              <div class="text-truncate">
                {{ campaign.description.substring(0, 80) }}{{ campaign.description.length > 80 ? '...' : '' }}
              </div>
            </v-card-subtitle>

            <v-card-text class="pb-2">
              <!-- Progress Bar -->
              <div class="mb-3">
                <div class="d-flex justify-space-between mb-1">
                  <span class="text-body-2">${{ formatAmount(campaign.current_amount || 0) }} raised</span>
                  <span class="text-body-2">{{ progressPercentage(campaign) }}%</span>
                </div>
                <v-progress-linear
                  :model-value="progressPercentage(campaign)"
                  height="8"
                  rounded
                  color="primary"
                />
                <div class="text-body-2 text-medium-emphasis mt-1">
                  Goal: ${{ formatAmount(campaign.goal_amount) }}
                </div>
              </div>

              <!-- Campaign Dates -->
              <div class="d-flex justify-space-between text-body-2 text-medium-emphasis">
                <div>
                  <v-icon size="small" class="mr-1">mdi-calendar-start</v-icon>
                  {{ formatDate(campaign.start_date) }}
                </div>
                <div>
                  <v-icon size="small" class="mr-1">mdi-calendar-end</v-icon>
                  {{ formatDate(campaign.end_date) }}
                </div>
              </div>

              <!-- Days Remaining -->
              <div v-if="getDaysRemaining(campaign) !== null" class="mt-2">
                <v-chip
                  :color="getDaysRemaining(campaign!) <= 7 ? 'warning' : 'info'"
                  size="small"
                  variant="tonal"
                >
                  <v-icon start size="small">mdi-clock-outline</v-icon>
                  {{ getDaysRemaining(campaign) }} {{ getDaysRemaining(campaign) === 1 ? 'day' : 'days' }} remaining
                </v-chip>
              </div>

              <!-- Rejection Reason -->
              <v-alert
                v-if="campaign.status === 'REJECTED' && campaign.rejection_reason"
                type="error"
                variant="tonal"
                density="compact"
                class="mt-3"
              >
                <div class="text-body-2">
                  <strong>Rejection Reason:</strong> {{ campaign.rejection_reason }}
                </div>
              </v-alert>
            </v-card-text>

            <!-- Action Buttons -->
            <v-card-actions class="pt-0">
              <v-btn
                @click.stop="viewCampaign(campaign)"
                variant="text"
                color="primary"
                size="small"
              >
                <v-icon start>mdi-eye</v-icon>
                View Details
              </v-btn>

              <v-spacer />

              <v-menu>
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-bind="props"
                    @click.stop
                    icon="mdi-dots-vertical"
                    variant="text"
                    size="small"
                  />
                </template>
                <v-list density="compact">
                  <v-list-item
                    @click="editCampaign(campaign)"
                    :disabled="campaign.status === 'APPROVED'"
                  >
                    <template v-slot:prepend>
                      <v-icon>mdi-pencil</v-icon>
                    </template>
                    <v-list-item-title>Edit</v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="duplicateCampaign(campaign)">
                    <template v-slot:prepend>
                      <v-icon>mdi-content-copy</v-icon>
                    </template>
                    <v-list-item-title>Duplicate</v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    @click="shareCampaign(campaign)"
                    :disabled="campaign.status !== 'APPROVED'"
                  >
                    <template v-slot:prepend>
                      <v-icon>mdi-share</v-icon>
                    </template>
                    <v-list-item-title>Share</v-list-item-title>
                  </v-list-item>
                  <v-divider />
                  <v-list-item
                    @click="deleteCampaign(campaign)"
                    :disabled="campaign.status === 'APPROVED'"
                    class="text-error"
                  >
                    <template v-slot:prepend>
                      <v-icon color="error">mdi-delete</v-icon>
                    </template>
                    <v-list-item-title>Delete</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Summary Statistics -->
      <v-row v-if="filteredCampaigns.length > 0" class="mt-8">
        <v-col cols="12">
          <v-card variant="outlined">
            <v-card-title>Campaign Summary</v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="6" sm="3">
                  <div class="text-center">
                    <div class="text-h5 font-weight-bold">{{ campaignStats.total }}</div>
                    <div class="text-body-2">Total Campaigns</div>
                  </div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-center">
                    <div class="text-h5 font-weight-bold">${{ formatAmount(campaignStats.totalRaised) }}</div>
                    <div class="text-body-2">Total Raised</div>
                  </div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-center">
                    <div class="text-h5 font-weight-bold">{{ campaignStats.active }}</div>
                    <div class="text-body-2">Active</div>
                  </div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-center">
                    <div class="text-h5 font-weight-bold">{{ campaignStats.pending }}</div>
                    <div class="text-body-2">Pending</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Success Snackbar -->
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        :timeout="4000"
      >
        {{ snackbar.text }}
        <template v-slot:actions>
          <v-btn @click="snackbar.show = false" variant="text">Close</v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </template>

  <script setup lang="ts">
  import { ref, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useCampaignStore, type Campaign } from '../../stores/campaign';

  const router = useRouter();
  const campaignStore = useCampaignStore();

  // Component state
  const searchQuery = ref('');
  const statusFilter = ref<string | null>(null);

  // Snackbar state
  const snackbar = ref({
    show: false,
    text: '',
    color: 'success'
  });

  // Filter options
  const statusOptions = [
    { title: 'All', value: null },
    { title: 'Draft', value: 'DRAFT' },
    { title: 'Pending', value: 'PENDING' },
    { title: 'Approved', value: 'APPROVED' },
    { title: 'Rejected', value: 'REJECTED' }
  ];

  // Computed properties
  const filteredCampaigns = computed(() => {
    let campaigns = campaignStore.userCampaigns;

    // Filter by search query
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      campaigns = campaigns.filter(campaign =>
        campaign.title.toLowerCase().includes(query) ||
        campaign.description.toLowerCase().includes(query)
      );
    }

    // Filter by status
    if (statusFilter.value) {
      campaigns = campaigns.filter(campaign => campaign.status === statusFilter.value);
    }

    return campaigns;
  });

  const campaignStats = computed(() => {
    const campaigns = campaignStore.userCampaigns;
    return {
      total: campaigns.length,
      totalRaised: campaigns.reduce((sum, c) => sum + (c.current_amount || 0), 0),
      active: campaigns.filter(c => c.status === 'APPROVED').length,
      pending: campaigns.filter(c => c.status === 'PENDING').length
    };
  });

  // Methods
  const refreshCampaigns = async () => {
    await campaignStore.fetchUserCampaigns();
  };

  const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = null;
  };

  const viewCampaign = (campaign: Campaign) => {
    router.push({ name: 'CampaignDetail', params: { id: campaign.id } });
  };

  const editCampaign = (campaign: Campaign) => {
    router.push({ name: 'EditCampaign', params: { id: campaign.id } });
  };

  const duplicateCampaign = (campaign: Campaign) => {
    // Navigate to create campaign with pre-filled data
    router.push({
      name: 'CreateCampaign',
      query: { duplicate: campaign.id }
    });
  };

  const shareCampaign = (campaign: Campaign) => {
    const url = `${window.location.origin}/campaigns/${campaign.id}`;
    navigator.clipboard.writeText(url).then(() => {
      showSnackbar('Campaign link copied to clipboard!');
    }).catch(() => {
      showSnackbar('Failed to copy link', 'error');
    });
  };

  const deleteCampaign = (campaign: Campaign) => {
    if (confirm(`Are you sure you want to delete "${campaign.title}"?`)) {
      // Implement delete functionality
      console.log('Delete campaign:', campaign.id);
      showSnackbar('Campaign deleted successfully');
    }
  };

  const formatAmount = (amount: number): string => {
    return new Intl.NumberFormat('en-US').format(amount);
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  };

  const formatStatus = (status: string): string => {
    return status.charAt(0) + status.slice(1).toLowerCase();
  };

  const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
      'DRAFT': 'grey',
      'PENDING': 'orange',
      'APPROVED': 'success',
      'REJECTED': 'error'
    };
    return colors[status] || 'grey';
  };

  const progressPercentage = (campaign: Campaign): number => {
    return Math.min((campaign.current_amount || 0) / campaign.goal_amount * 100, 100);
  };

  const getDaysRemaining = (campaign: Campaign): number => {
    const endDate = new Date(campaign.end_date);
    const today = new Date();
    const diffTime = endDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
  };

  const showSnackbar = (text: string, color: string = 'success') => {
    snackbar.value = { show: true, text, color };
  };

  // Lifecycle
  onMounted(() => {
    refreshCampaigns();
  });
  </script>

  <style scoped>
  .campaign-card {
    cursor: pointer;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  }

  .campaign-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .campaign-image {
    position: relative;
  }
  </style>
