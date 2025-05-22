<!-- resources/js/views/campaigns/CampaignListView.vue -->
<template>
    <v-container class="py-8">
      <!-- Hero Section -->
      <v-row class="mb-8">
        <v-col cols="12">
          <v-card color="primary" variant="flat" class="text-white">
            <v-card-text class="pa-8 text-center">
              <h1 class="text-h3 font-weight-bold mb-4">Support Great Causes</h1>
              <p class="text-h6 mb-6">
                Discover meaningful campaigns created by your colleagues and make a difference together
              </p>
              <v-btn
                :to="{ name: 'CreateCampaign' }"
                color="white"
                variant="elevated"
                size="large"
                class="text-primary"
              >
                <v-icon start>mdi-plus</v-icon>
                Start Your Campaign
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Search and Filters -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card>
            <v-card-text class="pb-2">
              <v-row>
                <!-- Search Bar -->
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="searchQuery"
                    placeholder="Search campaigns, causes, or keywords..."
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    density="comfortable"
                    clearable
                    hide-details
                    @keyup.enter="searchCampaigns"
                  />
                </v-col>

                <!-- Sort Options -->
                <v-col cols="12" sm="6" md="2">
                  <v-select
                    v-model="sortBy"
                    :items="sortOptions"
                    label="Sort by"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                  />
                </v-col>

                <!-- Search Button -->
                <v-col cols="12" md="2">
                  <v-btn
                    @click="searchCampaigns"
                    :loading="campaignStore.publicCampaignsLoading"
                    color="primary"
                    variant="elevated"
                    block
                    size="large"
                  >
                    <v-icon start>mdi-magnify</v-icon>
                    Search
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>

            <!-- Active Filters -->
            <v-card-text v-if="hasActiveFilters" class="pt-0">
              <div class="d-flex align-center flex-wrap gap-2">
                <span class="text-body-2 text-medium-emphasis mr-2">Active filters:</span>
                <v-chip
                  v-if="searchQuery"
                  closable
                  @click:close="searchQuery = ''"
                  size="small"
                  variant="outlined"
                >
                  Search: "{{ searchQuery }}"
                </v-chip>
                <v-btn
                  @click="clearFilters"
                  variant="text"
                  size="small"
                  color="primary"
                >
                  Clear All
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Results Summary -->
      <v-row v-if="!campaignStore.publicCampaignsLoading" class="mb-4">
        <v-col cols="12">
          <div class="d-flex align-center justify-space-between">
            <div class="text-body-1">
              <strong>{{ filteredCampaigns.length }}</strong>
              {{ filteredCampaigns.length === 1 ? 'campaign' : 'campaigns' }} found
              <span v-if="hasActiveFilters" class="text-medium-emphasis">
                with current filters
              </span>
            </div>
            <div class="d-flex align-center gap-2">
              <v-btn-toggle v-model="viewMode" mandatory variant="outlined" density="compact">
                <v-btn value="grid" icon="mdi-view-grid" />
                <v-btn value="list" icon="mdi-view-list" />
              </v-btn-toggle>
              <v-btn
                @click="refreshCampaigns"
                :loading="campaignStore.publicCampaignsLoading"
                icon="mdi-refresh"
                variant="text"
              />
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Loading State -->
      <v-row v-if="campaignStore.publicCampaignsLoading" justify="center">
        <v-col cols="12" class="text-center py-12">
          <v-progress-circular
            :size="60"
            :width="8"
            color="primary"
            indeterminate
          />
          <p class="mt-4 text-h6">Finding amazing campaigns...</p>
        </v-col>
      </v-row>

      <!-- Error State -->
      <v-row v-else-if="campaignStore.publicCampaignsError">
        <v-col cols="12">
          <v-alert type="error" prominent>
            <v-alert-title>Unable to Load Campaigns</v-alert-title>
            {{ campaignStore.publicCampaignsError }}
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

      <!-- No Results State -->
      <v-row v-else-if="filteredCampaigns.length === 0">
        <v-col cols="12">
          <v-card class="text-center pa-12" variant="outlined">
            <v-icon size="80" class="mb-4 text-medium-emphasis">
              {{ hasActiveFilters ? 'mdi-magnify-close' : 'mdi-bullhorn-outline' }}
            </v-icon>
            <h2 class="text-h5 mb-4">
              {{ hasActiveFilters ? 'No campaigns match your search' : 'No campaigns available' }}
            </h2>
            <p class="text-body-1 text-medium-emphasis mb-6">
              <span v-if="hasActiveFilters">
                Try adjusting your search criteria or browse all campaigns
              </span>
              <span v-else>
                Be the first to create a campaign and inspire others to contribute
              </span>
            </p>
            <div class="d-flex justify-center gap-4">
              <v-btn
                v-if="hasActiveFilters"
                @click="clearFilters"
                variant="outlined"
                color="primary"
              >
                Clear Filters
              </v-btn>
              <v-btn
                :to="{ name: 'CreateCampaign' }"
                color="primary"
                variant="elevated"
              >
                <v-icon start>mdi-plus</v-icon>
                Create Campaign
              </v-btn>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <!-- Campaigns Grid View -->
      <v-row v-else-if="viewMode === 'grid'">
        <v-col
          v-for="campaign in paginatedCampaigns"
          :key="campaign.id"
          cols="12"
          sm="6"
          lg="4"
        >
          <v-card
            class="campaign-card h-100"
            @click="viewCampaign(campaign)"
            elevation="2"
          >

            <!-- Campaign Content -->
            <v-card-title class="pb-2">
              <div class="text-truncate">{{ campaign.title }}</div>
            </v-card-title>

            <v-card-subtitle class="pb-1">
              <div class="d-flex align-center text-body-2">
                <v-icon size="small" class="mr-1">mdi-account</v-icon>
                by {{ campaign.user?.name || 'Anonymous' }}
              </div>
            </v-card-subtitle>

            <v-card-text class="pb-2">
              <!-- Description -->
              <p class="text-body-2 mb-3">
                {{ campaign.description.substring(0, 100) }}{{ campaign.description.length > 100 ? '...' : '' }}
              </p>

              <!-- Progress -->
              <div class="mb-3">
                <div class="d-flex justify-space-between align-center mb-2">
                  <span class="text-h6 font-weight-bold text-primary">
                    ${{ formatAmount(campaign.current_amount || 0) }}
                  </span>
                  <span class="text-body-2">
                    {{ progressPercentage(campaign) }}% funded
                  </span>
                </div>
                <v-progress-linear
                  :model-value="progressPercentage(campaign)"
                  height="8"
                  rounded
                  color="primary"
                  class="mb-2"
                />
                <div class="text-body-2 text-medium-emphasis">
                  Goal: ${{ formatAmount(campaign.goal_amount) }}
                </div>
              </div>

              <!-- Campaign Stats -->
              <div class="d-flex justify-space-between text-body-2 text-medium-emphasis">
                <div v-if="getDaysRemaining(campaign) !== null">
                  <v-icon size="small" class="mr-1">mdi-clock-outline</v-icon>
                  {{ getDaysRemaining(campaign) }} days left
                </div>
                <div v-else>
                  <v-icon size="small" class="mr-1">mdi-check-circle</v-icon>
                  Campaign ended
                </div>
                <div>
                  <v-icon size="small" class="mr-1">mdi-heart</v-icon>
                  {{ campaign.donations_count || 0 }} supporters
                </div>
              </div>
            </v-card-text>

            <!-- Action Buttons -->
            <v-card-actions class="pt-0">
              <v-btn
                @click.stop="viewCampaign(campaign)"
                variant="text"
                color="primary"
                size="small"
              >
                Learn More
              </v-btn>
              <v-spacer />
              <v-btn
                @click.stop="donateToCampaign(campaign)"
                :disabled="getDaysRemaining(campaign) === 0"
                color="primary"
                variant="elevated"
                size="small"
              >
                <v-icon start>mdi-heart</v-icon>
                Donate
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Campaigns List View -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-list>
              <template v-for="(campaign, index) in paginatedCampaigns" :key="campaign.id">
                <v-list-item
                  @click="viewCampaign(campaign)"
                  class="campaign-list-item py-4"
                >

                  <v-list-item-title class="text-h6 mb-1">
                    {{ campaign.title }}
                  </v-list-item-title>

                  <v-list-item-subtitle class="mb-2">
                    <div class="text-body-2 mb-1">
                      by {{ campaign.user?.name || 'Anonymous' }}
                    </div>
                    <div class="text-body-2">
                      {{ campaign.description.substring(0, 150) }}{{ campaign.description.length > 150 ? '...' : '' }}
                    </div>
                  </v-list-item-subtitle>

                  <template v-slot:append>
                    <div class="text-right" style="min-width: 200px;">
                      <div class="text-h6 font-weight-bold text-primary mb-1">
                        ${{ formatAmount(campaign.current_amount || 0) }}
                      </div>
                      <div class="text-body-2 text-medium-emphasis mb-2">
                        of ${{ formatAmount(campaign.goal_amount) }} goal
                      </div>
                      <v-progress-linear
                        :model-value="progressPercentage(campaign)"
                        height="6"
                        rounded
                        color="primary"
                        class="mb-2"
                      />
                      <div class="d-flex justify-space-between text-body-2">
                        <span>{{ progressPercentage(campaign) }}%</span>
                        <span v-if="getDaysRemaining(campaign) !== null">
                          {{ getDaysRemaining(campaign) }} days left
                        </span>
                      </div>
                      <v-btn
                        @click.stop="donateToCampaign(campaign)"
                        :disabled="getDaysRemaining(campaign) === 0"
                        color="primary"
                        size="small"
                        variant="elevated"
                        class="mt-2"
                      >
                        <v-icon start>mdi-heart</v-icon>
                        Donate
                      </v-btn>
                    </div>
                  </template>
                </v-list-item>
                <v-divider v-if="index < paginatedCampaigns.length - 1" />
              </template>
            </v-list>
          </v-card>
        </v-col>
      </v-row>

      <!-- Pagination -->
      <v-row v-if="filteredCampaigns.length > itemsPerPage" class="mt-6">
        <v-col cols="12" class="d-flex justify-center">
          <v-pagination
            v-model="currentPage"
            :length="totalPages"
            :total-visible="7"
            color="primary"
          />
        </v-col>
      </v-row>

      <!-- Quick Donate Dialog -->
      <v-dialog v-model="donateDialog" max-width="500">
        <v-card v-if="selectedCampaign">
          <v-card-title>
            <v-icon start>mdi-heart</v-icon>
            Support {{ selectedCampaign.title }}
          </v-card-title>
          <v-card-text>
            <p class="mb-4">
              Help reach the goal of ${{ formatAmount(selectedCampaign.goal_amount) }}
            </p>
            <div class="mb-4">
              <div class="text-body-2 mb-2">Currently raised:</div>
              <v-progress-linear
                :model-value="progressPercentage(selectedCampaign)"
                height="12"
                rounded
                color="primary"
              />
              <div class="d-flex justify-space-between text-body-2 mt-1">
                <span>${{ formatAmount(selectedCampaign.current_amount || 0) }}</span>
                <span>${{ formatAmount(selectedCampaign.goal_amount) }}</span>
              </div>
            </div>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn @click="donateDialog = false" variant="text">Cancel</v-btn>
            <v-btn
              @click="proceedToDonation"
              color="primary"
              variant="elevated"
            >
              Continue to Donate
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

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
  import { ref, computed, onMounted, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useCampaignStore, type Campaign } from '../../stores/campaign';

  const router = useRouter();
  const campaignStore = useCampaignStore();

  // Component state
  const searchQuery = ref('');
  const sortBy = ref('newest');
  const viewMode = ref<'grid' | 'list'>('grid');
  const currentPage = ref(1);
  const itemsPerPage = ref(12);

  // Dialog state
  const donateDialog = ref(false);
  const selectedCampaign = ref<Campaign | null>(null);

  // Snackbar state
  const snackbar = ref({
    show: false,
    text: '',
    color: 'success'
  });

  const sortOptions = [
    { title: 'Newest First', value: 'newest' },
    { title: 'Ending Soon', value: 'ending_soon' },
    { title: 'Most Funded', value: 'most_funded' },
    { title: 'Goal Amount (Low to High)', value: 'goal_low' },
    { title: 'Goal Amount (High to Low)', value: 'goal_high' },
    { title: 'Alphabetical', value: 'alphabetical' }
  ];

  // Computed properties
  const hasActiveFilters = computed(() => {
    return !!(searchQuery.value);
  });

  const filteredCampaigns = computed(() => {
    let campaigns = [...campaignStore.publicCampaigns];

    // Filter by search query
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      campaigns = campaigns.filter(campaign =>
        campaign.title.toLowerCase().includes(query) ||
        campaign.description.toLowerCase().includes(query)
      );
    }



    // Sort campaigns
    campaigns.sort((a, b) => {
      switch (sortBy.value) {
        case 'newest':
          return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
        case 'ending_soon':
          const daysA = getDaysRemaining(a) || Infinity;
          const daysB = getDaysRemaining(b) || Infinity;
          return daysA - daysB;
        case 'most_funded':
          return (b.current_amount || 0) - (a.current_amount || 0);
        case 'goal_low':
          return a.goal_amount - b.goal_amount;
        case 'goal_high':
          return b.goal_amount - a.goal_amount;
        case 'alphabetical':
          return a.title.localeCompare(b.title);
        default:
          return 0;
      }
    });

    return campaigns;
  });

  const totalPages = computed(() => {
    return Math.ceil(filteredCampaigns.value.length / itemsPerPage.value);
  });

  const paginatedCampaigns = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredCampaigns.value.slice(start, end);
  });

  // Methods
  const searchCampaigns = async () => {
    const params: { search?: string } = {};
    if (searchQuery.value) {
      params.search = searchQuery.value;
    }
    await campaignStore.fetchPublicCampaigns(params);
    currentPage.value = 1; // Reset to first page
  };

  const refreshCampaigns = async () => {
    await campaignStore.fetchPublicCampaigns();
  };

  const clearFilters = () => {
    searchQuery.value = '';
    currentPage.value = 1;
  };

  const viewCampaign = (campaign: Campaign) => {
    router.push({ name: 'CampaignDetail', params: { id: campaign.id } });
  };

  const donateToCampaign = (campaign: Campaign) => {
    selectedCampaign.value = campaign;
    donateDialog.value = true;
  };

  const proceedToDonation = () => {
    donateDialog.value = false;
    if (selectedCampaign.value) {
      router.push({
        name: 'Donate',
        params: { campaignId: selectedCampaign.value.id }
      });
    }
  };

  const formatAmount = (amount: number): string => {
    return new Intl.NumberFormat('en-US').format(amount);
  };

  const progressPercentage = (campaign: Campaign): number => {
    return Math.min((campaign.current_amount || 0) / campaign.goal_amount * 100, 100);
  };

  const getDaysRemaining = (campaign: Campaign): number | null => {
    const endDate = new Date(campaign.end_date);
    const today = new Date();
    const diffTime = endDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
  };

  const showSnackbar = (text: string, color: string = 'success') => {
    snackbar.value = { show: true, text, color };
  };

  // Watch for filter changes to reset pagination
  watch([searchQuery, sortBy], () => {
    currentPage.value = 1;
  });

  // Lifecycle
  onMounted(() => {
    refreshCampaigns();
  });
  </script>

  <style scoped>
  .campaign-card {
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .campaign-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  }

  .campaign-image {
    position: relative;
  }

  .campaign-list-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  .campaign-list-item:hover {
    background-color: rgba(0, 0, 0, 0.04);
  }
  </style>
