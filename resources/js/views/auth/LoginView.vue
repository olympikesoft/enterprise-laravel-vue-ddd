<!-- resources/js/views/auth/LoginView.vue -->
<template>
    <v-container class="fill-height" fluid>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="4">
          <v-card class="elevation-12">
            <v-toolbar color="primary" dark flat>
              <v-toolbar-title>Login</v-toolbar-title>
            </v-toolbar>

            <v-card-text>
              <v-form @submit.prevent="submitLogin">
                <!-- Error Alert -->

                <!-- Email Field -->
                <v-text-field
                  v-model="credentials.email"
                  label="Email"
                  type="email"
                  prepend-icon="mdi-email"
                  required
                  :disabled="authStore.isLoading"
                />

                <!-- Password Field -->
                <v-text-field
                  v-model="credentials.password"
                  label="Password"
                  type="password"
                  prepend-icon="mdi-lock"
                  required
                  :disabled="authStore.isLoading"
                />
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer />
              <v-btn
                color="primary"
                @click="submitLogin"
                :loading="authStore.isLoading"
                :disabled="!credentials.email || !credentials.password"
                block
              >
                Login
              </v-btn>
            </v-card-actions>

            <v-card-text class="text-center">
              <span class="text-body-2">
                Don't have an account?
                <router-link :to="{ name: 'Register' }" class="text-primary text-decoration-none">
                  Register here
                </router-link>
              </span>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>

  <script setup lang="ts">
  import { reactive } from 'vue';
  import { useAuthStore } from '../../stores/auth';

  const authStore = useAuthStore();
  const credentials = reactive({
    email: '',
    password: '',
  });

  const submitLogin = async () => {
    authStore.clearError();
    try {
      await authStore.login(credentials);
    } catch (error) {
      console.error("Login failed in component:", error);
    }
  };
  </script>

