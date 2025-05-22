<!-- resources/js/views/admin/AdminDashboard.vue -->
<template>
    <v-container fluid class="pa-6">
      <!-- Admin Header -->
      <v-row class="mb-6">
        <v-col cols="12">
          <div class="d-flex align-center justify-space-between">
            <div>
              <h1 class="text-h3 font-weight-bold mb-2">Admin Dashboard</h1>
              <p class="text-body-1 text-medium-emphasis">
                Manage campaigns, users, and system settings
              </p>
            </div>
            <v-chip color="primary" variant="elevated" size="large">
              <v-icon start>mdi-shield-account</v-icon>
              Administrator
            </v-chip>
          </div>
        </v-col>
      </v-row>

      <!-- Dashboard Statistics -->
      <v-row class="mb-8">
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start>mdi-chart-line</v-icon>
              System Overview
              <v-spacer />
              <v-btn
                @click="refreshStats"
                :loading="statsLoading"
                icon="mdi-refresh"
                variant="text"
                aria-label="Refresh statistics"
              />
            </v-card-title>

            <v-card-text>
              <v-row v-if="!statsLoading">
                <v-col cols="12" sm="6" md="3">
                  <v-card color="primary" variant="tonal" class="text-center pa-4">
                    <v-icon size="40" class="mb-2">mdi-account-group</v-icon>
                    <div class="text-h4 font-weight-bold">{{ stats.totalUsers || 0 }}</div>
                    <div class="text-body-2">Total Users</div>
                  </v-card>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <v-card color="success" variant="tonal" class="text-center pa-4">
                    <v-icon size="40" class="mb-2">mdi-bullhorn</v-icon>
                    <div class="text-h4 font-weight-bold">{{ stats.totalCampaigns || 0 }}</div>
                    <div class="text-body-2">Total Campaigns</div>
                  </v-card>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <v-card color="warning" variant="tonal" class="text-center pa-4">
                    <v-icon size="40" class="mb-2">mdi-clock-outline</v-icon>
                    <div class="text-h4 font-weight-bold">{{ stats.pendingCampaigns || 0 }}</div>
                    <div class="text-body-2">Pending Approval</div>
                  </v-card>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <v-card color="info" variant="tonal" class="text-center pa-4">
                    <v-icon size="40" class="mb-2">mdi-currency-usd</v-icon>
                    <div class="text-h4 font-weight-bold">${{ formatAmount(stats.totalDonations || 0) }}</div>
                    <div class="text-body-2">Total Donations</div>
                  </v-card>
                </v-col>
              </v-row>

              <v-row v-else>
                <v-col cols="12" class="text-center py-8">
                  <v-progress-circular indeterminate color="primary" />
                  <p class="mt-4">Loading statistics...</p>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Main Content Tabs -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-tabs v-model="activeTab" align-tabs="start" color="primary">
              <v-tab value="campaigns">
                <v-icon start>mdi-bullhorn</v-icon>
                Campaign Management
              </v-tab>
              <v-tab value="users">
                <v-icon start>mdi-account-group</v-icon>
                User Management
              </v-tab>
              <v-tab value="settings">
                <v-icon start>mdi-cog</v-icon>
                System Settings
              </v-tab>
            </v-tabs>

            <v-card-text class="pa-0">
              <v-tabs-window v-model="activeTab">

                <!-- Campaign Management Tab -->
                <v-tabs-window-item value="campaigns" class="pa-6">
                  <div class="d-flex align-center justify-space-between mb-4">
                    <h2 class="text-h5">Campaign Management</h2>
                    <div class="d-flex gap-2">
                      <v-btn-toggle v-model="campaignFilter" mandatory color="primary" variant="outlined" density="compact">
                        <v-btn value="all" size="small">All</v-btn>
                        <v-btn value="pending" size="small">Pending</v-btn>
                        <v-btn value="approved" size="small">Approved</v-btn>
                        <v-btn value="rejected" size="small">Rejected</v-btn>
                      </v-btn-toggle>
                      <v-btn
                        @click="refreshCampaigns"
                        :loading="campaignsLoading"
                        icon="mdi-refresh"
                        variant="text"
                        aria-label="Refresh campaigns"
                      />
                    </div>
                  </div>

                  <!-- Campaigns Data Table -->
                  <v-data-table
                    :headers="campaignHeaders"
                    :items="filteredCampaigns"
                    :loading="campaignsLoading"
                    class="elevation-1"
                    item-value="id"
                    :search="campaignSearch"
                    no-data-text="No campaigns found."
                    :items-per-page="10"
                  >
                    <template v-slot:item.title="{ item }">
                      <div class="font-weight-medium">{{ item.title }}</div>
                      <div class="text-body-2 text-medium-emphasis">
                        by {{ item.user?.name || 'Unknown User' }}
                      </div>
                    </template>

                    <template v-slot:item.goal_amount="{ item }">
                      <div class="font-weight-medium">${{ formatAmount(item.goal_amount) }}</div>
                      <div class="text-body-2 text-medium-emphasis">
                        Raised: ${{ formatAmount(item.current_amount || 0) }}
                      </div>
                    </template>

                    <template v-slot:item.status="{ item }">
                      <v-chip
                        :color="getStatusColor(item.status)"
                        size="small"
                        variant="flat"
                        label
                      >
                        {{ formatStatus(item.status) }}
                      </v-chip>
                    </template>

                    <template v-slot:item.created_at="{ item }">
                      {{ formatDate(item.created_at) }}
                    </template>

                    <template v-slot:item.actions="{ item }">
                      <div class="d-flex gap-1">
                        <v-btn
                          @click="viewCampaign(item)"
                          icon="mdi-eye-outline"
                          size="small"
                          variant="text"
                          title="View Campaign"
                        />
                        <v-btn
                          v-if="item.status === 'PENDING'"
                          @click="approveCampaign(item)"
                          icon="mdi-check-circle-outline"
                          size="small"
                          variant="text"
                          color="success"
                          :loading="actionLoading === item.id"
                          title="Approve Campaign"
                        />
                        <v-btn
                          v-if="item.status === 'PENDING'"
                          @click="openRejectDialog(item)"
                          icon="mdi-close-circle-outline"
                          size="small"
                          variant="text"
                          color="error"
                          :loading="actionLoading === item.id"
                          title="Reject Campaign"
                        />
                        <v-btn
                          @click="editCampaign(item)"
                          icon="mdi-pencil-outline"
                          size="small"
                          variant="text"
                          color="primary"
                          title="Edit Campaign"
                        />
                      </div>
                    </template>
                    <template v-slot:loading>
                        <v-skeleton-loader type="table-row@5"></v-skeleton-loader>
                    </template>
                    <template v-slot:no-data>
                      <div class="text-center pa-4">
                        <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-text-box-search-outline</v-icon>
                        <p>No campaigns found matching your criteria.</p>
                      </div>
                    </template>
                  </v-data-table>
                </v-tabs-window-item>

                <!-- User Management Tab -->
                <v-tabs-window-item value="users" class="pa-6">
                  <div class="d-flex align-center justify-space-between mb-4">
                    <h2 class="text-h5">User Management</h2>
                    <div class="d-flex gap-2">
                      <v-text-field
                        v-model="userSearch"
                        placeholder="Search users by name or email..."
                        prepend-inner-icon="mdi-magnify"
                        variant="outlined"
                        density="compact"
                        hide-details
                        clearable
                        style="min-width: 250px;"
                      />
                      <v-btn
                        @click="refreshUsers"
                        :loading="usersLoading"
                        icon="mdi-refresh"
                        variant="text"
                        aria-label="Refresh users"
                      />
                    </div>
                  </div>

                  <!-- Users Data Table -->
                  <v-data-table
                    :headers="userHeaders"
                    :items="filteredUsers"
                    :loading="usersLoading"
                    class="elevation-1"
                    item-value="id"
                    :search="userSearch"
                    no-data-text="No users found."
                    :items-per-page="10"
                  >
                    <template v-slot:item.name="{ item }">
                      <div class="d-flex align-center">
                        <v-avatar size="32" color="primary" class="mr-3">
                          <span class="text-body-2 font-weight-medium">{{ item.name.charAt(0).toUpperCase() }}</span>
                        </v-avatar>
                        <div>
                          <div class="font-weight-medium">{{ item.name }}</div>
                          <div class="text-body-2 text-medium-emphasis">{{ item.email }}</div>
                        </div>
                      </div>
                    </template>

                    <template v-slot:item.role="{ item }">
                      <v-chip
                        :color="item.role === 'admin' ? 'primary' : 'grey-darken-1'"
                        size="small"
                        variant="flat"
                        label
                      >
                        {{ item.role ? formatStatus(item.role) : 'Employee' }}
                      </v-chip>
                    </template>

                    <template v-slot:item.created_at="{ item }">
                      {{ formatDate(item.created_at) }}
                    </template>

                    <template v-slot:item.actions="{ item }">
                      <div class="d-flex gap-1">
                        <v-btn
                          @click="viewUser(item)"
                          icon="mdi-eye-outline"
                          size="small"
                          variant="text"
                          title="View User"
                        />
                        <v-btn
                          @click="editUser(item)"
                          icon="mdi-pencil-outline"
                          size="small"
                          variant="text"
                          color="primary"
                          title="Edit User"
                        />
                        <v-btn
                          @click="toggleUserRole(item)"
                          :icon="item.role === 'admin' ? 'mdi-shield-off-outline' : 'mdi-shield-plus-outline'"
                          size="small"
                          variant="text"
                          :color="item.role === 'admin' ? 'error' : 'warning'"
                          :loading="togglingRoleUserId === item.id"
                          :title="item.role === 'admin' ? 'Demote to Employee' : 'Promote to Admin'"
                        />
                      </div>
                    </template>
                     <template v-slot:loading>
                        <v-skeleton-loader type="table-row@5"></v-skeleton-loader>
                    </template>
                     <template v-slot:no-data>
                      <div class="text-center pa-4">
                        <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-search-outline</v-icon>
                        <p>No users found matching your criteria.</p>
                      </div>
                    </template>
                  </v-data-table>
                </v-tabs-window-item>

                <!-- System Settings Tab -->
                <v-tabs-window-item value="settings" class="pa-6">
                  <h2 class="text-h5 mb-6">System Settings</h2>

                  <v-row>
                    <v-col cols="12" md="6">
                      <v-card class="mb-4" elevation="2">
                        <v-card-title class="text-subtitle-1">Platform Settings</v-card-title>
                        <v-divider/>
                        <v-card-text>
                          <v-switch
                            v-model="settings.campaignApprovalRequired"
                            label="Require admin approval for new campaigns"
                            color="primary"
                            inset
                            hide-details
                            class="mb-3"
                          />
                          <v-switch
                            v-model="settings.allowPublicDonations"
                            label="Allow donations from non-employees"
                            color="primary"
                            inset
                            hide-details
                            class="mb-3"
                          />
                          <v-switch
                            v-model="settings.emailNotifications"
                            label="Enable system-wide email notifications"
                            color="primary"
                            inset
                            hide-details
                          />
                        </v-card-text>
                      </v-card>
                    </v-col>

                    <v-col cols="12" md="6">
                      <v-card class="mb-4" elevation="2">
                        <v-card-title class="text-subtitle-1">Donation Settings</v-card-title>
                        <v-divider/>
                        <v-card-text>
                          <v-text-field
                            v-model.number="settings.minimumDonation"
                            label="Minimum donation amount ($)"
                            type="number"
                            variant="outlined"
                            density="compact"
                            min="1"
                            prefix="$"
                            class="mb-4"
                            hide-details="auto"
                          />
                          <v-text-field
                            v-model.number="settings.maximumDonation"
                            label="Maximum donation amount ($)"
                            type="number"
                            variant="outlined"
                            density="compact"
                            min="1"
                            prefix="$"
                            hide-details="auto"
                          />
                        </v-card-text>
                      </v-card>
                    </v-col>
                  </v-row>

                  <v-btn
                    @click="saveSettings"
                    color="primary"
                    :loading="settingsLoading"
                    variant="elevated"
                    size="large"
                  >
                    <v-icon start>mdi-content-save</v-icon>
                    Save Settings
                  </v-btn>
                </v-tabs-window-item>
              </v-tabs-window>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Campaign Rejection Dialog -->
      <v-dialog v-model="rejectDialog" max-width="500px" persistent>
        <v-card>
          <v-card-title class="text-h6">Reject Campaign</v-card-title>
          <v-card-text>
            <p class="mb-4">
              Are you sure you want to reject the campaign "<strong>{{ selectedCampaign?.title }}</strong>"?
            </p>
            <v-textarea
              v-model="rejectionReason"
              label="Rejection reason"
              placeholder="Provide a reason for rejection (optional but recommended)"
              variant="outlined"
              rows="3"
              counter
              maxlength="500"
            />
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn @click="closeRejectDialog" variant="text">Cancel</v-btn>
            <v-btn
              @click="confirmRejectCampaign"
              color="error"
              :loading="actionLoading === selectedCampaign?.id"
              variant="elevated"
            >
              Confirm Rejection
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Success/Error Snackbar -->
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        :timeout="4000"
        location="top end"
      >
        {{ snackbar.text }}
        <template v-slot:actions>
          <v-btn @click="snackbar.show = false" variant="text" icon="mdi-close"/>
        </template>
      </v-snackbar>
    </v-container>
  </template>

  <script setup lang="ts">
  import { ref, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import apiClient from '../../services/api'; // Assuming apiClient is correctly set up

  // --- Interfaces for Type Safety ---
  interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'employee' | string; // string for flexibility if other roles exist
    created_at: string;
    // Add other user properties if returned by API and needed by UI
    is_active?: boolean;
  }

  interface Campaign {
    id: string | number; // Can be string (UUID) or number (auto-increment)
    title: string;
    user?: { id: number; name: string }; // Added user ID
    goal_amount: number;
    current_amount?: number;
    status: 'PENDING' | 'APPROVED' | 'REJECTED' | 'DRAFT' | string; // Ensure these match backend
    created_at: string;
    rejection_reason?: string;
    // Add other campaign properties if returned by API and needed by UI
    description?: string;
    end_date?: string;
    image_url?: string;
  }

  interface AdminSettings {
    campaignApprovalRequired: boolean;
    allowPublicDonations: boolean;
    emailNotifications: boolean;
    minimumDonation: number;
    maximumDonation: number;
  }

  // --- Component State ---
  const router = useRouter();
  const activeTab = ref('campaigns');

  // Loading states
  const statsLoading = ref(false);
  const campaignsLoading = ref(false);
  const usersLoading = ref(false);
  const settingsLoading = ref(false);
  const actionLoading = ref<string | number | null>(null); // For campaign approve/reject (ID)
  const togglingRoleUserId = ref<number | null>(null); // For user role toggle (ID)

  // Data
  const stats = ref({
    totalUsers: 0,
    totalCampaigns: 0,
    pendingCampaigns: 0,
    totalDonations: 0
  });

  const campaigns = ref<Campaign[]>([]);
  const users = ref<User[]>([]);
  const campaignFilter = ref('all');
  const userSearch = ref(''); // For user table search
  const campaignSearch = ref(''); // For campaign table search (if needed, not currently implemented in template)

  // Dialog state
  const rejectDialog = ref(false);
  const selectedCampaign = ref<Campaign | null>(null);
  const rejectionReason = ref('');

  // Settings
  const settings = ref<AdminSettings>({
    campaignApprovalRequired: true,
    allowPublicDonations: false,
    emailNotifications: true,
    minimumDonation: 1,
    maximumDonation: 10000
  });

  // Snackbar
  const snackbar = ref({
    show: false,
    text: '',
    color: 'success'
  });

  // --- Table Headers ---
  const campaignHeaders = [
    { title: 'Campaign', key: 'title', sortable: true, minWidth: '250px' },
    { title: 'Goal Amount', key: 'goal_amount', sortable: true, minWidth: '180px' },
    { title: 'Status', key: 'status', sortable: true, minWidth: '120px' },
    { title: 'Created', key: 'created_at', sortable: true, minWidth: '150px' },
    { title: 'Actions', key: 'actions', sortable: false, width: '180px' }
  ];

  const userHeaders = [
    { title: 'User', key: 'name', sortable: true, minWidth: '250px' },
    { title: 'Role', key: 'role', sortable: true, minWidth: '120px' },
    { title: 'Joined', key: 'created_at', sortable: true, minWidth: '150px' },
    { title: 'Actions', key: 'actions', sortable: false, width: '150px' }
  ];

  // --- Computed Properties ---
  const filteredCampaigns = computed(() => {
    let filtered = campaigns.value;
    if (campaignFilter.value !== 'all') {
      filtered = filtered.filter(c => c.status.toUpperCase() === campaignFilter.value.toUpperCase());
    }
    // Add campaignSearch logic if you implement a search field for campaigns
    // if (campaignSearch.value) {
    //   const search = campaignSearch.value.toLowerCase().trim();
    //   filtered = filtered.filter(c => c.title.toLowerCase().includes(search));
    // }
    return filtered;
  });

  const filteredUsers = computed(() => {
    if (!userSearch.value) return users.value;
    const search = userSearch.value.toLowerCase().trim();
    return users.value.filter(u =>
      u.name.toLowerCase().includes(search) ||
      u.email.toLowerCase().includes(search)
    );
  });

  // --- Methods ---
  const showSnackbar = (text: string, color: string = 'success') => {
    snackbar.value = { show: true, text, color };
  };

  // Data Fetching
  const refreshStats = async () => {
    statsLoading.value = true;
    try {
      const response = await apiClient.get('/admin/dashboard-stats');
      stats.value = response.data.data;
    } catch (error: any) {
      console.error('Failed to load statistics:', error);
      showSnackbar(error.response?.data?.message || 'Failed to load statistics', 'error');
    } finally {
      statsLoading.value = false;
    }
  };

  const refreshCampaigns = async () => {
    campaignsLoading.value = true;
    try {
      const response = await apiClient.get('/admin/campaigns'); // Ensure this endpoint returns all necessary campaign fields
      campaigns.value = response.data.data;
    } catch (error: any) {
      console.error('Failed to load campaigns:', error);
      showSnackbar(error.response?.data?.message || 'Failed to load campaigns', 'error');
    } finally {
      campaignsLoading.value = false;
    }
  };

  const refreshUsers = async () => {
    usersLoading.value = true;
    try {
      const response = await apiClient.get('/admin/users');
      users.value = response.data.data;
    } catch (error: any) {
      console.error('Failed to load users:', error);
      showSnackbar(error.response?.data?.message || 'Failed to load users', 'error');
    } finally {
      usersLoading.value = false;
    }
  };

  const loadSettings = async () => {
    settingsLoading.value = true; // Show loading for initial load as well
    try {
      const response = await apiClient.get('/admin/settings'); // Assumed API endpoint
      if (response.data && response.data.data) {
        settings.value = response.data.data;
      }
    } catch (error: any) {
      console.error('Failed to load system settings:', error);
      showSnackbar(error.response?.data?.message || 'Failed to load system settings. Using defaults.', 'warning');
    } finally {
      settingsLoading.value = false;
    }
  };

  // Campaign Actions
  const approveCampaign = async (campaign: Campaign) => {
    actionLoading.value = campaign.id;
    try {
      await apiClient.post(`/admin/campaigns/${campaign.id}/approve`);
      // More robust UI update:
      campaigns.value = campaigns.value.map(c =>
        c.id === campaign.id ? { ...c, status: 'APPROVED' } : c
      );
      showSnackbar(`Campaign "${campaign.title}" approved successfully`, 'success');
      await refreshStats(); // Update stats (e.g., pending count)
    } catch (error: any) {
      console.error('Failed to approve campaign:', error);
      showSnackbar(error.response?.data?.message || 'Failed to approve campaign', 'error');
    } finally {
      actionLoading.value = null;
    }
  };

  const openRejectDialog = (campaign: Campaign) => {
    selectedCampaign.value = campaign;
    rejectionReason.value = campaign.rejection_reason || '';
    rejectDialog.value = true;
  };

  const closeRejectDialog = () => {
    rejectDialog.value = false;
    selectedCampaign.value = null;
    rejectionReason.value = '';
  };

  const confirmRejectCampaign = async () => {
    if (!selectedCampaign.value) return;

    const campaignToUpdate = selectedCampaign.value;
    actionLoading.value = campaignToUpdate.id;
    try {
      await apiClient.post(`/admin/campaigns/${campaignToUpdate.id}/reject`, {
        reason: rejectionReason.value,
      });
      // More robust UI update:
      campaigns.value = campaigns.value.map(c =>
        c.id === campaignToUpdate.id ? { ...c, status: 'REJECTED', rejection_reason: rejectionReason.value } : c
      );
      closeRejectDialog();
      showSnackbar(`Campaign "${campaignToUpdate.title}" rejected`, 'success');
      await refreshStats(); // Update stats
    } catch (error: any) {
      console.error('Failed to reject campaign:', error);
      showSnackbar(error.response?.data?.message || 'Failed to reject campaign', 'error');
    } finally {
      actionLoading.value = null; // Ensure this is reset even if dialog isn't closed by success
    }
  };

  const viewCampaign = (campaign: Campaign) => {
    // Ensure your router is configured for this named route
    // And that the CampaignDetail page can handle an admin viewing it or has a specific AdminCampaignDetail
    router.push({ name: 'CampaignDetail', params: { id: campaign.id } });
  };

  const editCampaign = (campaign: Campaign) => {
    // Ensure your router is configured. This might be an 'AdminEditCampaign' route
    // or your 'EditCampaign' route needs to handle admin privileges.
    router.push({ name: 'AdminEditCampaign', params: { id: campaign.id } }); // Example: specific admin edit route
  };

  // User Actions
  const viewUser = (user: User) => {
    router.push({ name: 'AdminUserView', params: { id: user.id } });
  };

  const editUser = (user: User) => {
    router.push({ name: 'AdminUserEdit', params: { id: user.id } });
  };

  const toggleUserRole = async (user: User) => {
    togglingRoleUserId.value = user.id;
    try {
      const newRole = user.role === 'admin' ? 'employee' : 'admin';
      // API endpoint expects role: newRole
      await apiClient.put(`/admin/users/${user.id}`, { role: newRole });

      // More robust UI update:
      users.value = users.value.map(u =>
        u.id === user.id ? { ...u, role: newRole } : u
      );
      showSnackbar(`User ${user.name}'s role updated to ${formatStatus(newRole)}`, 'success');
    } catch (error: any) {
      console.error('Failed to update user role:', error);
      showSnackbar(error.response?.data?.message || 'Failed to update user role', 'error');
    } finally {
      togglingRoleUserId.value = null;
    }
  };

  // Settings Actions
  const saveSettings = async () => {
    settingsLoading.value = true;
    try {
      // Ensure your backend expects the settings.value structure
      await apiClient.post('/admin/settings', settings.value);
      showSnackbar('Settings saved successfully', 'success');
    } catch (error: any) {
      console.error('Failed to save settings:', error);
      showSnackbar(error.response?.data?.message || 'Failed to save settings', 'error');
    } finally {
      settingsLoading.value = false;
    }
  };

  // Formatting Utilities
  const formatAmount = (amount?: number): string => {
    if (typeof amount !== 'number' || isNaN(amount)) return '0';
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
  };

  const formatDate = (dateString?: string): string => {
    if (!dateString) return 'N/A';
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return 'Invalid Date';
      return date.toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
      });
    } catch (e) {
      return 'Invalid Date';
    }
  };

  const getStatusColor = (status?: string): string => {
    if (!status) return 'grey';
    const upperStatus = status.toUpperCase();
    const colors: Record<string, string> = {
      'PENDING': 'orange-darken-1',
      'APPROVED': 'success',
      'REJECTED': 'error',
      'DRAFT': 'blue-grey',
      // Add other statuses if they exist
    };
    return colors[upperStatus] || 'grey-darken-1'; // Default for unknown statuses
  };

  const formatStatus = (status?: string): string => {
    if (!status) return 'N/A';
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase().replace(/_/g, ' '); // Replace underscores
  };

  // --- Lifecycle ---
  onMounted(() => {
    refreshStats();
    refreshCampaigns();
    refreshUsers();
    loadSettings();
  });
  </script>

  <style scoped>
  .v-tabs-window-item {
    min-height: 500px;
  }
  .gap-1 {
    gap: 0.25rem;
  }
  .gap-2 {
    gap: 0.5rem;
  }
  .v-row > .v-col > .v-card.text-center { /* Specificity for stat cards */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%; /* Ensure stat cards fill column height */
  }
  .v-data-table {
    --v-table-header-height: 48px; /* Adjust if needed */
  }
  </style>
