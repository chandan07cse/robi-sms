<template>
  <div class="page-container">
    <div class="page-content space-y-4 sm:space-y-6">
      <div class="card p-4 sm:p-6">
        <!-- API Configuration -->
        <div class="mb-6 sm:mb-8">
          <h3 class="text-base sm:text-lg font-semibold mb-4 flex items-center gap-2">
            <Settings2 :size="20" />
            API Configuration
          </h3>
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
              <div>
                <label class="block text-xs sm:text-sm font-medium mb-2">API Username</label>
                <input 
                  v-model="settings.api_username" 
                  type="text" 
                  class="input w-full text-sm sm:text-base" 
                  placeholder="Your API username"
                  :disabled="!editMode"
                >
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium mb-2">API Password</label>
                <input 
                  v-model="settings.api_password" 
                  type="password" 
                  class="input w-full text-sm sm:text-base" 
                  placeholder="Your API password"
                  :disabled="!editMode"
                >
                <p class="text-xs text-gray-500 mt-1">Leave empty to keep current password</p>
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium mb-2">Default Sender ID</label>
                <input 
                  v-model="settings.default_sender" 
                  type="text" 
                  class="input w-full text-sm sm:text-base" 
                  placeholder="e.g., 880XXXXXXXXXX"
                  :disabled="!editMode"
                >
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium mb-2">API Base URL</label>
                <input 
                  v-model="settings.api_base_url" 
                  type="text" 
                  class="input w-full text-sm sm:text-base" 
                  placeholder="https://api.mobireach.com.bd"
                  :disabled="!editMode"
                >
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-6">
              <button 
                v-if="!editMode"
                @click="editMode = true" 
                class="btn btn-primary w-full sm:w-auto"
              >
                <Edit :size="16" />
                Edit Settings
              </button>
              <template v-else>
                <button 
                  @click="saveSettings" 
                  class="btn btn-primary w-full sm:w-auto"
                  :disabled="saving"
                >
                  <Save :size="16" />
                  {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>
                <button 
                  @click="cancelEdit" 
                  class="btn btn-secondary w-full sm:w-auto"
                  :disabled="saving"
                >
                  <X :size="16" />
                  Cancel
                </button>
              </template>
              <button 
                @click="testConnection" 
                class="btn btn-secondary w-full sm:w-auto"
                :disabled="testing"
              >
                <Zap :size="16" />
                {{ testing ? 'Testing...' : 'Test Connection' }}
              </button>
            </div>

            <!-- Connection Status -->
            <div v-if="connectionStatus" class="mt-4 p-3 sm:p-4 rounded-lg text-sm" :class="{
              'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200': connectionStatus.success,
              'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200': !connectionStatus.success
            }">
              <p class="font-medium">{{ connectionStatus.message }}</p>
              <div v-if="connectionStatus.success && connectionStatus.balance" class="mt-2 text-xs sm:text-sm">
                <p>GUI Balance: {{ connectionStatus.balance.guiBalance }} BDT</p>
                <p>API Balance: {{ connectionStatus.balance.apiBalance }} BDT</p>
              </div>
            </div>
          </div>
        </div>

      <!-- Dashboard Settings -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <Layout :size="20" />
          Dashboard Settings
        </h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div>
              <p class="font-medium">Real-time Updates</p>
              <p class="text-sm text-gray-500">Enable live SMS tracking via WebSocket (requires Socket.IO server)</p>
            </div>
            <button 
              @click="toggleRealtimeUpdates" 
              class="w-12 h-6 rounded-full relative transition-colors"
              :class="realtimeUpdates ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
            >
              <div 
                class="w-5 h-5 bg-white rounded-full absolute top-0.5 transition-transform"
                :class="realtimeUpdates ? 'right-0.5' : 'left-0.5'"
              ></div>
            </button>
          </div>

          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div>
              <p class="font-medium">Auto-refresh</p>
              <p class="text-sm text-gray-500">Automatically refresh dashboard data every 30 seconds</p>
            </div>
            <button 
              @click="toggleAutoRefresh"
              class="w-12 h-6 rounded-full relative transition-colors"
              :class="autoRefresh ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
            >
              <div 
                class="w-5 h-5 bg-white rounded-full absolute top-0.5 transition-transform"
                :class="autoRefresh ? 'right-0.5' : 'left-0.5'"
              ></div>
            </button>
          </div>

          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div>
              <p class="font-medium">Notifications</p>
              <p class="text-sm text-gray-500">Show toast notifications for SMS events</p>
            </div>
            <button 
              @click="toggleNotifications"
              class="w-12 h-6 rounded-full relative transition-colors"
              :class="notifications ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
            >
              <div 
                class="w-5 h-5 bg-white rounded-full absolute top-0.5 transition-transform"
                :class="notifications ? 'right-0.5' : 'left-0.5'"
              ></div>
            </button>
          </div>
          
          <!-- Warning for Real-time Updates -->
          <div v-if="realtimeUpdates" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
              <strong>Note:</strong> Real-time updates require a Socket.IO server running on port 3000. 
              If you see WebSocket errors in the console, disable this option or start the server.
            </p>
          </div>
        </div>
      </div>

      <!-- Redis Configuration -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <Database :size="20" />
          Redis Storage
        </h3>
        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Connection</label>
              <input type="text" value="default" class="input w-full" disabled>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Key Prefix</label>
              <input type="text" value="adarearch:" class="input w-full" disabled>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Data Retention (Days)</label>
              <input type="number" value="30" class="input w-full" disabled>
            </div>
            <div class="flex items-end">
              <button class="btn btn-secondary w-full">
                <Trash2 :size="16" />
                Clear Old Data
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- System Information -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <Info :size="20" />
          System Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <p class="text-sm text-gray-500">Package Version</p>
            <p class="font-medium text-lg">v1.0.0</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <p class="text-sm text-gray-500">Laravel Version</p>
            <p class="font-medium text-lg">11.x</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <p class="text-sm text-gray-500">PHP Version</p>
            <p class="font-medium text-lg">8.2+</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <p class="text-sm text-gray-500">Dashboard Port</p>
            <p class="font-medium text-lg">8090</p>
          </div>
        </div>
      </div>

      <!-- Developer Credits -->
      <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <Code :size="20" />
          Developer Credits
        </h3>
        <div class="bg-gradient-to-r from-primary-50 to-purple-50 dark:from-primary-900/20 dark:to-purple-900/20 rounded-lg p-6 border-2 border-primary-200 dark:border-primary-800">
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
              MN
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Developed by</p>
              <p class="text-2xl font-bold text-primary-900 dark:text-primary-100">Md. Ainul Moin Noor</p>
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Full Stack Developer</p>
            </div>
          </div>
          <div class="mt-4 flex gap-4">
            <a href="https://github.com" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
              <Globe :size="14" />
              GitHub
            </a>
            <a href="mailto:noor@example.com" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
              <Mail :size="14" />
              Email
            </a>
          </div>
        </div>
      </div>

      <!-- Package Information -->
      <div class="mt-8 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <h4 class="font-semibold mb-2 flex items-center gap-2">
          <Package :size="18" />
          AdaReach SMS Laravel Package
        </h4>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
          A comprehensive Laravel package for integrating Robi/AdaReach Business SMS API with real-time dashboard monitoring,
          similar to Laravel Telescope and Horizon.
        </p>
        <div class="flex gap-4 text-sm">
          <a href="https://github.com/chandan07cse/robi-sms" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
            GitHub Repository
          </a>
          <a href="https://packagist.org/packages/chandan07cse/robi-sms" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
            Packagist
          </a>
          <span class="text-gray-500">License: MIT</span>
        </div>
      </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Settings2, Layout, Database, Info, Code, Globe, Mail, Package, Trash2, Edit, Save, X, Zap } from 'lucide-vue-next';
import api from '../services/api';

const settings = ref({
  api_username: '',
  api_password: '',
  default_sender: '',
  api_base_url: 'https://api.mobireach.com.bd'
});

const originalSettings = ref({});
const editMode = ref(false);
const saving = ref(false);
const testing = ref(false);
const connectionStatus = ref(null);

// Dashboard Settings
const realtimeUpdates = ref(localStorage.getItem('realtimeUpdates') === 'true');
const autoRefresh = ref(localStorage.getItem('autoRefresh') === 'true');
const notifications = ref(localStorage.getItem('notifications') !== 'false'); // Default true

onMounted(async () => {
  await loadSettings();
});

const loadSettings = async () => {
  try {
    const response = await api.get('/settings');
    if (response.data.success) {
      const loadedSettings = response.data.settings;
      settings.value = {
        api_username: loadedSettings.api_username?.value || '',
        api_password: '',  // Never load password for security
        default_sender: loadedSettings.default_sender?.value || '',
        api_base_url: loadedSettings.api_base_url?.value || 'https://api.mobireach.com.bd'
      };
      originalSettings.value = { ...settings.value };
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
};

const saveSettings = async () => {
  saving.value = true;
  connectionStatus.value = null;
  
  try {
    // Only send fields that have values
    const payload = {};
    if (settings.value.api_username) payload.api_username = settings.value.api_username;
    if (settings.value.api_password) payload.api_password = settings.value.api_password;
    if (settings.value.default_sender) payload.default_sender = settings.value.default_sender;
    if (settings.value.api_base_url) payload.api_base_url = settings.value.api_base_url;

    const response = await api.post('/settings', payload);
    
    if (response.data.success) {
      connectionStatus.value = {
        success: true,
        message: 'Settings saved successfully!'
      };
      originalSettings.value = { ...settings.value };
      settings.value.api_password = ''; // Clear password field after save
      editMode.value = false;
    }
  } catch (error) {
    let errorMessage = 'Failed to save settings';
    
    if (error.response?.status === 419) {
      errorMessage = 'Session expired. Please refresh the page and try again.';
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    connectionStatus.value = {
      success: false,
      message: errorMessage
    };
  } finally {
    saving.value = false;
  }
};

const cancelEdit = () => {
  settings.value = { ...originalSettings.value };
  settings.value.api_password = '';
  editMode.value = false;
  connectionStatus.value = null;
};

const testConnection = async () => {
  testing.value = true;
  connectionStatus.value = null;
  
  try {
    const response = await api.post('/settings/test-connection');
    
    if (response.data.success) {
      connectionStatus.value = {
        success: true,
        message: 'Connection successful!',
        balance: response.data.balance
      };
    }
  } catch (error) {
    let errorMessage = 'Connection failed';
    
    if (error.response?.status === 419) {
      errorMessage = 'Session expired. Please refresh the page and try again.';
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    connectionStatus.value = {
      success: false,
      message: errorMessage
    };
  } finally {
    testing.value = false;
  }
};

// Dashboard Settings Toggles
const toggleRealtimeUpdates = () => {
  realtimeUpdates.value = !realtimeUpdates.value;
  localStorage.setItem('realtimeUpdates', realtimeUpdates.value);
  
  // Show message to reload page for changes to take effect
  if (realtimeUpdates.value) {
    connectionStatus.value = {
      success: true,
      message: 'Real-time updates enabled. Reload the page to connect to WebSocket.'
    };
  } else {
    connectionStatus.value = {
      success: true,
      message: 'Real-time updates disabled. Reload the page to disconnect WebSocket.'
    };
  }
};

const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value;
  localStorage.setItem('autoRefresh', autoRefresh.value);
};

const toggleNotifications = () => {
  notifications.value = !notifications.value;
  localStorage.setItem('notifications', notifications.value);
};
</script>
