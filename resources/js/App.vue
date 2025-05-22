<!-- resources/js/App.vue -->
<template>
    <v-app>
      <!-- Main Navigation Bar -->
      <v-app-bar
        app
        color="primary"
        dark
        elevation="4"
        height="70"
      >
        <!-- Logo/Brand -->
        <v-app-bar-title class="d-flex align-center">
          <v-icon size="32" class="mr-2">mdi-hands-helping</v-icon>
          <router-link
            :to="{ name: 'Home' }"
            class="text-white text-decoration-none font-weight-bold"
          >
            ACME CSR Platform
          </router-link>
        </v-app-bar-title>

        <v-spacer />

        <!-- Desktop Navigation -->
        <template v-if="!mobile">
          <!-- Public Links -->
          <v-btn
            :to="{ name: 'CampaignListView' }"
            variant="text"
            color="white"
            class="mr-2"
          >
            <v-icon start>mdi-bullhorn</v-icon>
            Browse Campaigns
          </v-btn>

          <!-- Authenticated User Links -->
          <template v-if="authStore.isAuthenticated">
            <v-btn
              :to="{ name: 'Dashboard' }"
              variant="text"
              color="white"
              class="mr-2"
            >
              <v-icon start>mdi-view-dashboard</v-icon>
              Dashboard
            </v-btn>

            <v-btn
              :to="{ name: 'CreateCampaign' }"
              variant="text"
              color="white"
              class="mr-2"
            >
              <v-icon start>mdi-plus</v-icon>
              Create Campaign
            </v-btn>

            <v-btn
              :to="{ name: 'MyCampaigns' }"
              variant="text"
              color="white"
              class="mr-2"
            >
              <v-icon start>mdi-folder</v-icon>
              My Campaigns
            </v-btn>

            <!-- Admin Panel (Admin Only) -->
            <v-btn
              v-if="authStore.isAdmin"
              :to="{ name: 'AdminDashboard' }"
              variant="text"
              color="white"
              class="mr-4"
            >
              <v-icon start>mdi-shield-account</v-icon>
              Admin Panel
            </v-btn>

            <!-- User Menu -->
            <v-menu offset-y>
              <template v-slot:activator="{ props }">
                <v-btn
                  v-bind="props"
                  variant="text"
                  color="white"
                  class="text-none"
                >
                  <v-avatar size="32" class="mr-2">
                    <span class="text-body-2">{{ userInitials }}</span>
                  </v-avatar>
                  {{ authStore.user?.name }}
                  <v-icon end>mdi-chevron-down</v-icon>
                </v-btn>
              </template>
              <v-list>
                <v-list-item
                  :to="{ name: 'MyDonations' }"
                  prepend-icon="mdi-heart"
                >
                  <v-list-item-title>My Donations</v-list-item-title>
                </v-list-item>
                <v-divider />
                <v-list-item
                  @click="handleLogout"
                  prepend-icon="mdi-logout"
                  class="text-error"
                >
                  <v-list-item-title>Logout</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>

          <!-- Guest Links -->
          <template v-else>
            <v-btn
              :to="{ name: 'Login' }"
              variant="outlined"
              color="white"
              class="mr-2"
            >
              Login
            </v-btn>
            <v-btn
              :to="{ name: 'Register' }"
              variant="flat"
              color="white"
              class="text-primary"
            >
              Register
            </v-btn>
          </template>
        </template>

        <!-- Mobile Menu Button -->
        <v-app-bar-nav-icon
          v-if="mobile"
          @click="drawer = !drawer"
        />
      </v-app-bar>

      <!-- Mobile Navigation Drawer -->
      <v-navigation-drawer
        v-model="drawer"
        app
        temporary
        width="280"
      >
        <!-- User Profile Section (if authenticated) -->
        <template v-if="authStore.isAuthenticated">
          <v-list-item class="pa-4">
            <template v-slot:prepend>
              <v-avatar size="48" color="primary">
                <span class="text-h6">{{ userInitials }}</span>
              </v-avatar>
            </template>
            <v-list-item-title class="font-weight-medium">
              {{ authStore.user?.name }}
            </v-list-item-title>
            <v-list-item-subtitle>
              {{ authStore.user?.email }}
            </v-list-item-subtitle>
          </v-list-item>
          <v-divider />
        </template>

        <!-- Navigation Items -->
        <v-list density="compact" nav>
          <!-- Public Links -->
          <v-list-item
            :to="{ name: 'Home' }"
            prepend-icon="mdi-home"
            title="Home"
            @click="drawer = false"
          />
          <v-list-item
            :to="{ name: 'CampaignListView' }"
            prepend-icon="mdi-bullhorn"
            title="Browse Campaigns"
            @click="drawer = false"
          />

          <!-- Authenticated User Links -->
          <template v-if="authStore.isAuthenticated">
            <v-divider class="my-2" />
            <v-list-subheader>My Account</v-list-subheader>
            <v-list-item
              :to="{ name: 'CreateCampaign' }"
              prepend-icon="mdi-plus"
              title="Create Campaign"
              @click="drawer = false"
            />
            <v-list-item
              :to="{ name: 'MyCampaigns' }"
              prepend-icon="mdi-folder"
              title="My Campaigns"
              @click="drawer = false"
            />
            <v-list-item
              :to="{ name: 'MyDonations' }"
              prepend-icon="mdi-heart"
              title="My Donations"
              @click="drawer = false"
            />

            <!-- Admin Section -->
            <template v-if="authStore.isAdmin">
              <v-divider class="my-2" />
              <v-list-subheader>Administration</v-list-subheader>
              <v-list-item
                :to="{ name: 'AdminDashboard' }"
                prepend-icon="mdi-shield-account"
                title="Admin Dashboard"
                @click="drawer = false"
              />
            </template>


            <v-list-item
              @click="handleLogout"
              prepend-icon="mdi-logout"
              title="Logout"
              class="text-error"
            />
          </template>

          <!-- Guest Links -->
          <template v-else>
            <v-divider class="my-2" />
            <v-list-item
              :to="{ name: 'Login' }"
              prepend-icon="mdi-login"
              title="Login"
              @click="drawer = false"
            />
            <v-list-item
              :to="{ name: 'Register' }"
              prepend-icon="mdi-account-plus"
              title="Register"
              @click="drawer = false"
            />
          </template>
        </v-list>

        <!-- Footer in Drawer -->
        <template v-slot:append>
          <div class="pa-4 text-center">
            <div class="text-body-2 text-medium-emphasis">
              © {{ currentYear }} ACME Corp
            </div>
            <div class="text-caption text-medium-emphasis">
              CSR Platform v1.0
            </div>
          </div>
        </template>
      </v-navigation-drawer>

      <!-- Main Content Area -->
      <v-main>
        <!-- Loading Overlay -->
        <v-overlay
          v-model="authStore.loading"
          class="d-flex align-center justify-center"
          persistent
        >
          <div class="text-center">
            <v-progress-circular
              :size="60"
              :width="8"
              color="primary"
              indeterminate
            />
            <p class="mt-4 text-h6">Loading ACME CSR Platform...</p>
          </div>
        </v-overlay>

        <!-- Router View -->
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </v-main>

      <!-- Footer -->
      <v-footer
        app
        color="grey-lighten-4"
        class="text-center pa-4"
      >
        <div class="w-100">
          <div class="d-flex justify-center align-center flex-wrap mb-2">
            <v-btn
              href="#"
              variant="text"
              size="small"
              class="mx-2"
            >
              About
            </v-btn>
            <v-btn
              href="#"
              variant="text"
              size="small"
              class="mx-2"
            >
              Privacy Policy
            </v-btn>
            <v-btn
              href="#"
              variant="text"
              size="small"
              class="mx-2"
            >
              Terms of Service
            </v-btn>
            <v-btn
              href="#"
              variant="text"
              size="small"
              class="mx-2"
            >
              Contact
            </v-btn>
          </div>
          <div class="text-body-2 text-medium-emphasis">
            © {{ currentYear }} ACME Corporation. All rights reserved.
          </div>
          <div class="text-caption text-medium-emphasis">
            Empowering employees to make a difference through corporate social responsibility
          </div>
        </div>
      </v-footer>

    </v-app>
  </template>

  <script setup lang="ts">
  import { ref, computed, onMounted, watch } from 'vue';
  import { useDisplay } from 'vuetify';
  import { useRouter, useRoute } from 'vue-router';
  import { useAuthStore } from './stores/auth';

  const router = useRouter();
  const route = useRoute();
  const authStore = useAuthStore();
  const { mobile } = useDisplay();

  // Component state
  const drawer = ref(false);
  const notification = ref({
    show: false,
    text: '',
    color: 'success',
    timeout: 4000
  });

  // Computed properties
  const currentYear = computed(() => new Date().getFullYear());

  const userInitials = computed(() => {
    if (!authStore.user?.name) return 'U';
    return authStore.user.name
      .split(' ')
      .map(name => name.charAt(0))
      .join('')
      .toUpperCase()
      .substring(0, 2);
  });

  // Methods
  const handleLogout = async () => {
    try {
      await authStore.logout();
      showNotification('Successfully logged out', 'success');
    } catch (error) {
      showNotification('Logout failed', 'error');
    }
  };

  const showNotification = (text: string, color: string = 'success', timeout: number = 4000) => {
    notification.value = { show: true, text, color, timeout };
  };

  // Close drawer when route changes (mobile)
  watch(route, () => {
    if (mobile.value) {
      drawer.value = false;
    }
  });

  // Global error handling
  window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
    showNotification('An unexpected error occurred', 'error');
  });

  // Initialize app
  onMounted(() => {
    // Any app initialization logic
  });
  </script>

  <style scoped>
  /* Page transitions */
  .page-enter-active,
  .page-leave-active {
    transition: all 0.3s ease;
  }

  .page-enter-from {
    opacity: 0;
    transform: translateX(10px);
  }

  .page-leave-to {
    opacity: 0;
    transform: translateX(-10px);
  }

  /* Custom link styling */
  .text-decoration-none {
    text-decoration: none !important;
  }

  .text-decoration-none:hover {
    text-decoration: none !important;
  }
  </style>
