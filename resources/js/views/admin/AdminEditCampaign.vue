<!-- resources/js/views/admin/AdminEditCampaign.vue -->
<template>
    <v-container fluid class="pa-6">
      <v-row>
        <v-col cols="12">
          <div class="d-flex align-center mb-6">
            <v-btn icon="mdi-arrow-left" variant="text" @click="goBack" class="mr-2"></v-btn>
            <div>
              <h1 class="text-h4 font-weight-bold">Edit Campaign</h1>
              <p class="text-body-1 text-medium-emphasis">
                Modify details for campaign: {{ campaign.title || 'Loading...' }}
              </p>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-row v-if="loading">
        <v-col cols="12" class="text-center">
          <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
          <p class="mt-4">Loading campaign details...</p>
        </v-col>
      </v-row>

      <v-row v-else-if="!campaign.id">
         <v-col cols="12">
          <v-alert type="error" prominent border="start">
            Campaign not found or failed to load.
            <v-btn color="white" variant="outlined" @click="fetchCampaignDetails" class="ml-4">Retry</v-btn>
          </v-alert>
        </v-col>
      </v-row>

      <v-form @submit.prevent="submitUpdateCampaign" v-else ref="editCampaignForm">
        <v-row>
          <!-- Campaign Details Column -->
          <v-col cols="12" md="8">
            <v-card class="mb-6">
              <v-card-title>Campaign Information</v-card-title>
              <v-divider />
              <v-card-text>
                <v-text-field
                  v-model="editableCampaign.title"
                  label="Campaign Title"
                  variant="outlined"
                  :rules="[rules.required, rules.maxLength(100)]"
                  counter="100"
                  class="mb-4"
                ></v-text-field>

                <v-textarea
                  v-model="editableCampaign.description"
                  label="Campaign Description"
                  variant="outlined"
                  :rules="[rules.required, rules.maxLength(2000)]"
                  counter="2000"
                  rows="5"
                  auto-grow
                  class="mb-4"
                ></v-textarea>

                <v-row>
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model.number="editableCampaign.goal_amount"
                      label="Goal Amount ($)"
                      type="number"
                      variant="outlined"
                      prefix="$"
                      :rules="[rules.required, rules.minValue(1)]"
                      class="mb-4"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model="editableCampaign.end_date"
                      label="End Date (YYYY-MM-DD)"
                      type="date"
                      variant="outlined"
                      :rules="[rules.required, rules.validDate]"
                      class="mb-4"
                      hint="Campaign will end at midnight on this date."
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                </v-row>

              </v-card-text>
            </v-card>
          </v-col>

          <!-- Settings & Status Column -->
          <v-col cols="12" md="4">
            <v-card class="mb-6">
              <v-card-title>Status & Settings</v-card-title>
              <v-divider />
              <v-card-text>
                <v-select
                  v-model="editableCampaign.status"
                  :items="campaignStatuses"
                  item-title="text"
                  item-value="value"
                  label="Campaign Status"
                  variant="outlined"
                  :rules="[rules.required]"
                  class="mb-4"
                ></v-select>


                <p class="text-body-2 mb-1">Created by:</p>
                <p class="font-weight-medium mb-4">
                  {{ campaign.user?.name || 'N/A' }} (ID: {{ campaign.user?.id || 'N/A' }})
                </p>

                <p class="text-body-2 mb-1">Created At:</p>
                <p class="font-weight-medium mb-4">{{ formatDate(campaign.created_at) }}</p>

                <p class="text-body-2 mb-1">Last Updated At:</p>
                <p class="font-weight-medium">{{ formatDate(campaign.updated_at) }}</p>

                <v-textarea
                  v-if="editableCampaign.status === 'rejected'"
                  v-model="editableCampaign.rejection_reason"
                  label="Rejection Reason"
                  variant="outlined"
                  rows="3"
                  class="mt-4"
                  placeholder="Provide a reason if rejecting"
                ></v-textarea>

              </v-card-text>
            </v-card>

            <v-card>
              <v-card-text>
                <v-btn
                  color="primary"
                  block
                  size="large"
                  type="submit"
                  :loading="submitting"
                  variant="elevated"
                >
                  <v-icon start>mdi-content-save</v-icon>
                  Save Changes
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-form>

      <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="4000" location="top end">
        {{ snackbar.text }}
        <template v-slot:actions>
          <v-btn @click="snackbar.show = false" variant="text" icon="mdi-close" />
        </template>
      </v-snackbar>
    </v-container>
  </template>

  <script setup lang="ts">
  import { ref, onMounted, reactive, watch } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useAdminStore } from '../../stores/admin';

  interface UserStub {
    id: number;
    name: string;
  }

  interface Campaign {
    id: string | number | null;
    title: string;
    description: string;
    goal_amount: number;
    current_amount?: number;
    end_date: string; // Expecting YYYY-MM-DD format
    status: 'pending' | 'approved' | 'rejected' | 'active' | 'completed' | 'cancelled' | string;
    user?: UserStub;
    rejection_reason?: string;
    created_at?: string;
    updated_at?: string;
  }
  const route = useRoute();
  const router = useRouter();
  const adminStore = useAdminStore();

  const campaignId = ref<string | null>(null);
  let campaign = ref<Partial<Campaign>>({}); // Original fetched campaign
  const editableCampaign = reactive<Partial<Campaign>>({}); // Form data
  const loading = ref(true);
  const submitting = ref(false);

  const editCampaignForm = ref<any>(null); // For Vuetify form validation

  const snackbar = ref({
    show: false,
    text: '',
    color: 'success',
  });

  const campaignStatuses = [
    { text: 'Pending Approval', value: 'pending' },
    { text: 'Approved (Active)', value: 'approved' },
    { text: 'Rejected', value: 'rejected' },
    { text: 'Cancelled', value: 'cancelled' },
    { text: 'Completed', value: 'completed' },
  ];

  const rules = {
    required: (value: any) => !!value || 'This field is required.',
    maxLength: (len: number) => (value: string) => (value && value.length <= len) || `Max ${len} characters.`,
    minValue: (min: number) => (value: number) => (value && value >= min) || `Must be at least ${min}.`,
    validDate: (value: string) => {
      if (!value) return true; // Allow empty if not required, handle with 'required' rule
      const date = new Date(value);
      return !isNaN(date.getTime()) || 'Invalid date format.';
    },
  };

  const fetchCampaignDetails = async () => {
    if (!campaignId.value) return;
    loading.value = true;
    try {
      // Use the admin store method
      const campaignData = await adminStore.fetchCampaign(campaignId.value);
      campaign.value = campaignData || {};

      // Initialize editableCampaign with fetched data
      Object.assign(editableCampaign, JSON.parse(JSON.stringify(campaignData)));

      // Format date for input type="date"
      if (editableCampaign.end_date) {
          const date = new Date(editableCampaign.end_date);
          if (!isNaN(date.getTime())) {
              editableCampaign.end_date = date.toISOString().split('T')[0];
          } else {
              editableCampaign.end_date = ''; // Clear if invalid
          }
      }

    } catch (error: any) {
      console.error('Failed to fetch campaign details:', error);
      showSnackbar(error.response?.data?.message || 'Failed to load campaign details.', 'error');
      campaign.value = { id: null }; // Mark as failed to load
    } finally {
      loading.value = false;
    }
  };

  const showSnackbar = (message: string, color: string) => {
      snackbar.value.text = message;
      snackbar.value.color = color;
      snackbar.value.show = true;
  };

  const submitUpdateCampaign = async () => {
    if (!editCampaignForm.value) return;
    const { valid } = await editCampaignForm.value.validate();

    if (!valid || !campaignId.value) {
      showSnackbar('Please correct the errors in the form.', 'warning');
      return;
    }

    submitting.value = true;

    try {
      // Prepare payload by removing read-only fields
      const payload = { ...editableCampaign };
      delete payload.user;
      delete payload.created_at;
      delete payload.updated_at;
      delete payload.current_amount;

      const updatedCampaign = await adminStore.updateCampaign(campaignId.value, payload);

      // Update local state
      campaign.value = updatedCampaign;
      Object.assign(editableCampaign, JSON.parse(JSON.stringify(updatedCampaign)));

      // Re-format date after successful update
      if (editableCampaign.end_date) {
          const date = new Date(editableCampaign.end_date);
          if (!isNaN(date.getTime())) {
              editableCampaign.end_date = date.toISOString().split('T')[0];
          }
      }

      showSnackbar('Campaign updated successfully!', 'success');

      // Optionally navigate back or refresh parent list
      // router.push({ name: 'AdminDashboard', query: { tab: 'campaigns' } });

    } catch (error: any) {
      console.error('Failed to update campaign:', error);
      const errorMsg = error.response?.data?.message || 'Failed to update campaign.';
      let details = '';
      if (error.response?.data?.errors) {
          details = Object.values(error.response.data.errors).flat().join(' ');
      }
      showSnackbar(`${errorMsg} ${details}`.trim(), 'error');
    } finally {
      submitting.value = false;
    }
  };

  const goBack = () => {
    router.back(); // Or router.push({ name: 'AdminDashboard', query: { tab: 'campaigns' } });
  };

  const formatDate = (dateString?: string): string => {
    if (!dateString) return 'N/A';
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return 'Invalid Date';
      return date.toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
      });
    } catch (e) {
      return 'Invalid Date';
    }
  };

  watch(
    () => editableCampaign.status,
    (newStatus) => {
      if (newStatus !== 'REJECTED' && editableCampaign.rejection_reason) {
        editableCampaign.rejection_reason = ''; // Clear rejection reason if status is not REJECTED
      }
    }
  );

  onMounted(() => {
    const idFromRoute = route.params.id;
    if (idFromRoute) {
      campaignId.value = Array.isArray(idFromRoute) ? idFromRoute[0] : idFromRoute;
      fetchCampaignDetails();
    } else {
      showSnackbar('Campaign ID not found in route.', 'error');
      loading.value = false;
      campaign.value = { id: null };
    }
  });
  </script>

  <style scoped>
  .v-card {
    overflow: visible; /* Allow date pickers etc. to show correctly */
  }
  .mb-4 {
    margin-bottom: 16px !important;
  }
  </style>
