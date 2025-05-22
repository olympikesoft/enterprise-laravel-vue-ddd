<!-- resources/js/views/campaigns/CreateCampaignView.vue -->
<template>
    <v-container class="py-8">
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card class="elevation-4">
            <!-- Header -->
            <v-card-title class="pa-6 pb-4">
              <div class="d-flex align-center">
                <v-icon size="30" class="mr-3" color="primary">mdi-bullhorn-plus</v-icon>
                <div>
                  <h1 class="text-h4 font-weight-bold">Create Campaign</h1>
                  <p class="text-body-2 text-medium-emphasis ma-0">
                    Start your fundraising journey and make a difference
                  </p>
                </div>
              </div>
            </v-card-title>

            <v-divider />

            <!-- Form -->
            <v-card-text class="pa-6">
              <v-form ref="formRef" @submit.prevent="submitCampaign">
                <!-- Error Alert -->
                <v-alert
                  v-if="campaignStore.createCampaignError"
                  type="error"
                  class="mb-6"
                  dismissible
                  @click:close="campaignStore.createCampaignError = null"
                >
                  <v-alert-title>Campaign Creation Failed</v-alert-title>
                  {{ campaignStore.createCampaignError }}
                </v-alert>

                <!-- Campaign Title -->
                <v-text-field
                  v-model="form.title"
                  label="Campaign Title"
                  placeholder="Enter a compelling campaign title"
                  prepend-icon="mdi-format-title"
                  variant="outlined"
                  :rules="[rules.required, rules.minLength(5), rules.maxLength(255)]"
                  :disabled="campaignStore.createCampaignLoading"
                  counter="255"
                  class="mb-4"
                  hint="Make it catchy and descriptive (5-255 characters)"
                  persistent-hint
                />

                <!-- Campaign Description -->
                <v-textarea
                  v-model="form.description"
                  label="Campaign Description"
                  placeholder="Tell people why this cause matters and how their donations will make a difference..."
                  prepend-icon="mdi-text"
                  variant="outlined"
                  :rules="[rules.required, rules.minLength(20)]"
                  :disabled="campaignStore.createCampaignLoading"
                  rows="5"
                  counter
                  class="mb-4"
                  hint="Provide detailed information about your campaign (minimum 20 characters)"
                  persistent-hint
                />

                <!-- Fundraising Goal -->
                <v-text-field
                  v-model.number="form.goal_amount"
                  label="Fundraising Goal"
                  placeholder="10000"
                  prepend-icon="mdi-currency-usd"
                  variant="outlined"
                  type="number"
                  :rules="[rules.required, rules.minAmount]"
                  :disabled="campaignStore.createCampaignLoading"
                  prefix="$"
                  class="mb-4"
                  hint="Set a realistic and achievable goal amount"
                  persistent-hint
                />

                <!-- Date Range -->
                <v-row class="mb-4">
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model="form.start_date"
                      label="Start Date"
                      type="date"
                      prepend-icon="mdi-calendar-start"
                      variant="outlined"
                      :rules="[rules.required, rules.startDateValid]"
                      :disabled="campaignStore.createCampaignLoading"
                      :min="minDate"
                      hint="Campaign start date"
                      persistent-hint
                    />
                  </v-col>
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model="form.end_date"
                      label="End Date"
                      type="date"
                      prepend-icon="mdi-calendar-end"
                      variant="outlined"
                      :rules="[rules.required, rules.endDateValid]"
                      :disabled="campaignStore.createCampaignLoading"
                      :min="form.start_date || minDate"
                      hint="Campaign end date"
                      persistent-hint
                    />
                  </v-col>
                </v-row>

                <!-- Campaign Duration Info -->
                <v-alert
                  v-if="campaignDuration"
                  type="info"
                  variant="tonal"
                  class="mb-4"
                >
                  <div class="d-flex align-center">
                    <v-icon start>mdi-information</v-icon>
                    Campaign Duration: {{ campaignDuration }} days
                  </div>
                </v-alert>

                <!-- Preview Card -->
                <v-card
                  v-if="isFormPartiallyFilled"
                  variant="outlined"
                  class="mb-6"
                >
                  <v-card-title class="pb-2">
                    <v-icon start>mdi-eye</v-icon>
                    Campaign Preview
                  </v-card-title>
                  <v-card-text>
                    <div class="mb-2">
                      <strong>{{ form.title || 'Campaign Title' }}</strong>
                    </div>
                    <div class="text-body-2 mb-3">
                      {{ form.description ? form.description.substring(0, 100) + (form.description.length > 100 ? '...' : '') : 'Campaign description will appear here...' }}
                    </div>
                    <div class="d-flex justify-space-between text-body-2">
                      <span>Goal: ${{ formatAmount(form.goal_amount || 0) }}</span>
                      <span v-if="form.start_date && form.end_date">
                        {{ formatDate(form.start_date) }} - {{ formatDate(form.end_date) }}
                      </span>
                    </div>
                  </v-card-text>
                </v-card>

                <!-- Terms and Conditions -->
                <v-checkbox
                  v-model="acceptTerms"
                  :rules="[rules.acceptTerms]"
                  :disabled="campaignStore.createCampaignLoading"
                  color="primary"
                  class="mb-4"
                >
                  <template v-slot:label>
                    <div class="text-body-2">
                      I agree to the
                      <a href="#" @click.prevent="showTerms = true" class="text-primary">
                        Terms and Conditions
                      </a>
                      and confirm that this campaign complies with company policies
                    </div>
                  </template>
                </v-checkbox>
              </v-form>
            </v-card-text>

            <!-- Action Buttons -->
            <v-card-actions class="pa-6 pt-0">
              <v-btn
                @click="saveDraft"
                variant="outlined"
                color="secondary"
                :disabled="campaignStore.createCampaignLoading"
                class="mr-2"
              >
                <v-icon start>mdi-content-save-outline</v-icon>
                Save Draft
              </v-btn>

              <v-spacer />

              <v-btn
                @click="goBack"
                variant="text"
                :disabled="campaignStore.createCampaignLoading"
                class="mr-2"
              >
                Cancel
              </v-btn>

              <v-btn
                @click="submitCampaign"
                color="primary"
                variant="elevated"
                :loading="campaignStore.createCampaignLoading"
                :disabled="!isFormValid || !acceptTerms"
                size="large"
              >
                <v-icon start>mdi-send</v-icon>
                Create Campaign
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Success Dialog -->
      <v-dialog v-model="successDialog" max-width="400" persistent>
        <v-card>
          <v-card-text class="text-center pa-8">
            <v-icon size="60" color="success" class="mb-4">mdi-check-circle</v-icon>
            <h2 class="text-h5 mb-4">Campaign Created!</h2>
            <p class="text-body-1 mb-4">
              Your campaign has been submitted for review. You'll be notified once it's approved.
            </p>
          </v-card-text>
          <v-card-actions class="justify-center pb-6">
            <v-btn
              @click="goToDashboard"
              color="primary"
              variant="elevated"
            >
              Go to Dashboard
            </v-btn>
            <v-btn
              @click="goToCampaigns"
              variant="outlined"
              class="ml-2"
            >
              View My Campaigns
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Terms Dialog -->
      <v-dialog v-model="showTerms" max-width="600">
        <v-card>
          <v-card-title>Terms and Conditions</v-card-title>
          <v-card-text>
            <p>By creating a campaign, you agree to:</p>
            <ul class="ml-4">
              <li>Provide accurate and truthful information</li>
              <li>Use funds only for the stated purpose</li>
              <li>Comply with all company policies</li>
              <li>Provide updates on campaign progress</li>
              <li>Allow admin review and approval process</li>
            </ul>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn @click="showTerms = false" variant="text">Close</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </template>

  <script setup lang="ts">
  import { ref, computed, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useCampaignStore, type CampaignFormData } from '../../stores/campaign';

  const router = useRouter();
  const campaignStore = useCampaignStore();

  // Form ref
  const formRef = ref();

  // Component state
  const successDialog = ref(false);
  const showTerms = ref(false);
  const acceptTerms = ref(false);

  // Form data
  const form = ref<CampaignFormData>({
    title: '',
    description: '',
    goal_amount: null,
    start_date: '',
    end_date: ''
  });

  // Validation rules
  const rules = {
    required: (value: any) => !!value || 'This field is required',
    minLength: (min: number) => (value: string) =>
      (value && value.length >= min) || `Minimum ${min} characters required`,
    maxLength: (max: number) => (value: string) =>
      (value && value.length <= max) || `Maximum ${max} characters allowed`,
    minAmount: (value: number) => value >= 1 || 'Goal amount must be at least $1',
    startDateValid: (value: string) => {
      if (!value) return 'Start date is required';
      const selectedDate = new Date(value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return selectedDate >= today || 'Start date must be today or in the future';
    },
    endDateValid: (value: string) => {
      if (!value) return 'End date is required';
      if (!form.value.start_date) return 'Please select start date first';
      const startDate = new Date(form.value.start_date);
      const endDate = new Date(value);
      return endDate > startDate || 'End date must be after start date';
    },
    acceptTerms: (value: boolean) => value || 'You must accept the terms and conditions'
  };

  // Computed properties
  const minDate = computed(() => {
    return new Date().toISOString().split('T')[0];
  });

  const campaignDuration = computed(() => {
    if (!form.value.start_date || !form.value.end_date) return null;
    const start = new Date(form.value.start_date);
    const end = new Date(form.value.end_date);
    const diffTime = end.getTime() - start.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : null;
  });

  const isFormPartiallyFilled = computed(() => {
    return form.value.title || form.value.description || form.value.goal_amount;
  });

  const isFormValid = computed(() => {
    return form.value.title &&
           form.value.description &&
           form.value.goal_amount &&
           form.value.start_date &&
           form.value.end_date &&
           form.value.title.length >= 5 &&
           form.value.description.length >= 20 &&
           form.value.goal_amount >= 1;
  });

  // Methods
  const submitCampaign = async () => {
    // Validate form first
    const { valid } = await formRef.value.validate();
    if (!valid || !acceptTerms.value) return;

    try {
      await campaignStore.createCampaign(form.value);
      successDialog.value = true;
      resetForm();
    } catch (error) {
      // Error is handled by the store and displayed in the alert
      console.error('Campaign creation failed:', error);
    }
  };

  const saveDraft = () => {
    // Save to localStorage or send to backend as draft
    localStorage.setItem('campaignDraft', JSON.stringify(form.value));
    // Show success message
    alert('Draft saved successfully!');
  };

  const resetForm = () => {
    form.value = {
      title: '',
      description: '',
      goal_amount: null,
      start_date: '',
      end_date: ''
    };
    acceptTerms.value = false;
    formRef.value?.resetValidation();
  };

  const goBack = () => {
    router.push({ name: 'UserDashboard' });
  };

  const goToDashboard = () => {
    successDialog.value = false;
    router.push({ name: 'UserDashboard' });
  };

  const goToCampaigns = () => {
    successDialog.value = false;
    router.push({ name: 'MyCampaigns' });
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

  // Load draft on component mount
  const loadDraft = () => {
    const draft = localStorage.getItem('campaignDraft');
    if (draft) {
      try {
        const parsedDraft = JSON.parse(draft);
        if (confirm('Load saved draft?')) {
          form.value = parsedDraft;
        }
      } catch (error) {
        console.error('Failed to load draft:', error);
      }
    }
  };

  // Watch for form changes to auto-save draft
  watch(form, (newForm) => {
    if (Object.values(newForm).some(value => value)) {
      localStorage.setItem('campaignDraft', JSON.stringify(newForm));
    }
  }, { deep: true });

  // Load draft on mount
  loadDraft();
  </script>

  <style scoped>
  .v-card {
    border-radius: 12px;
  }

  .v-alert {
    border-radius: 8px;
  }
  </style>
