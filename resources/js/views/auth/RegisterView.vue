<!-- resources/js/views/auth/RegisterView.vue -->
<template>
    <v-container class="fill-height" fluid>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="5">
          <v-card class="elevation-12">
            <v-toolbar color="primary" dark flat>
              <v-toolbar-title>Register</v-toolbar-title>
            </v-toolbar>

            <v-card-text>
              <v-form @submit.prevent="submitRegister">
                <!-- Error Alert -->
                <v-alert
                  v-if="authStore.error"
                  type="error"
                  class="mb-4"
                  dismissible
                  @click:close="authStore.clearError"
                >
                  {{ authStore.error }}
                </v-alert>

                <!-- Success Alert -->
                <v-alert
                  v-if="successMessage"
                  type="success"
                  class="mb-4"
                  dismissible
                  @click:close="successMessage = ''"
                >
                  {{ successMessage }}
                </v-alert>

                <!-- Name Field -->
                <v-text-field
                  v-model="userData.name"
                  label="Name"
                  type="text"
                  prepend-icon="mdi-account"
                  required
                  :disabled="authStore.isLoading"
                  :rules="[rules.required]"
                />

                <!-- Email Field -->
                <v-text-field
                  v-model="userData.email"
                  label="Email"
                  type="email"
                  prepend-icon="mdi-email"
                  required
                  :disabled="authStore.isLoading"
                  :rules="[rules.required, rules.email]"
                />

                <!-- Password Field -->
                <v-text-field
                  v-model="userData.password"
                  label="Password"
                  :type="showPassword ? 'text' : 'password'"
                  prepend-icon="mdi-lock"
                  :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  @click:append="showPassword = !showPassword"
                  required
                  :disabled="authStore.isLoading"
                  :rules="[rules.required, rules.minLength]"
                  hint="At least 8 characters"
                  persistent-hint
                />

                <!-- Confirm Password Field -->
                <v-text-field
                  v-model="userData.password_confirmation"
                  label="Confirm Password"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  prepend-icon="mdi-lock-check"
                  :append-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  @click:append="showConfirmPassword = !showConfirmPassword"
                  required
                  :disabled="authStore.isLoading"
                  :rules="[rules.required, rules.passwordMatch]"
                  :error-messages="passwordMismatchError"
                />
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer />
              <v-btn
                color="primary"
                @click="submitRegister"
                :loading="authStore.isLoading"
                :disabled="!isFormValid"
                block
                size="large"
              >
                Register
              </v-btn>
            </v-card-actions>

            <v-card-text class="text-center">
              <span class="text-body-2">
                Already have an account?
                <router-link :to="{ name: 'Login' }" class="text-primary text-decoration-none">
                  Login here
                </router-link>
              </span>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>

  <script setup lang="ts">
  import { reactive, ref, computed } from 'vue';
  import { useAuthStore } from '../../stores/auth';
  import { useRouter } from 'vue-router';

  const authStore = useAuthStore();
  const router = useRouter();
  const successMessage = ref('');
  const showPassword = ref(false);
  const showConfirmPassword = ref(false);

  const userData = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  // Validation rules
  const rules = {
    required: (value: string) => !!value || 'This field is required',
    email: (value: string) => {
      const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return pattern.test(value) || 'Please enter a valid email address';
    },
    minLength: (value: string) => value.length >= 8 || 'Password must be at least 8 characters',
    passwordMatch: (value: string) => value === userData.password || 'Passwords do not match'
  };

  // Computed properties
  const passwordMismatchError = computed(() => {
    if (userData.password_confirmation && userData.password !== userData.password_confirmation) {
      return 'Passwords do not match';
    }
    return '';
  });

  const isFormValid = computed(() => {
    return userData.name &&
           userData.email &&
           userData.password &&
           userData.password_confirmation &&
           userData.password === userData.password_confirmation &&
           userData.password.length >= 8;
  });

  const submitRegister = async () => {
    authStore.clearError();
    successMessage.value = '';

    // Double-check password match
    if (userData.password !== userData.password_confirmation) {
      authStore.error = 'Passwords do not match.';
      return;
    }

    try {
      await authStore.register(userData);
      successMessage.value = 'Registration successful! Redirecting to login...';

      // Redirect to login after success
      setTimeout(() => {
        if (!authStore.error) {
          router.push({ name: 'Login' });
        }
      }, 2000);
    } catch (error) {
      // Error is already set in the store
      console.error('Registration failed:', error);
    }
  };
  </script>
