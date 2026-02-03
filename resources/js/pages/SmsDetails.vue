<template>
  <div class="page-container">
    <div class="page-content">
      <div v-if="loading" class="flex items-center justify-center min-h-screen">
        <div class="loading-spinner w-12 h-12"></div>
      </div>

      <div v-else-if="sms" class="space-y-4 sm:space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
      <button @click="goBack" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
        <ArrowLeft :size="20" class="sm:w-6 sm:h-6" />
      </button>
      <div class="flex-1">
        <h2 class="text-xl sm:text-2xl font-bold">SMS Details</h2>
        <p class="text-xs sm:text-sm text-gray-500">ID: {{ sms.id }}</p>
      </div>
      <span :class="['badge text-sm sm:text-base px-3 sm:px-4 py-2', `badge-${getStatusColor(sms.status)}`]">
        <component :is="getStatusIcon(sms.status)" :size="14" class="sm:w-4 sm:h-4" />
        {{ sms.status }}
      </span>
    </div>

    <!-- Main Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
      <div class="lg:col-span-2 card p-4 sm:p-6 space-y-4 sm:space-y-6">
        <div>
          <h3 class="text-base sm:text-lg font-semibold mb-4">Message Content</h3>
          <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 sm:p-4">
            <p class="whitespace-pre-wrap text-sm sm:text-base">{{ sms.message }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label class="text-xs sm:text-sm font-medium text-gray-500">Phone Number</label>
            <p class="mt-1 text-base sm:text-lg font-medium flex items-center gap-2">
              <Phone :size="14" class="sm:w-4 sm:h-4" />
              {{ sms.phone }}
            </p>
          </div>

          <div>
            <label class="text-xs sm:text-sm font-medium text-gray-500">Sender ID</label>
            <p class="mt-1 text-base sm:text-lg font-medium">{{ sms.sender }}</p>
          </div>

          <div>
            <label class="text-xs sm:text-sm font-medium text-gray-500">Message Type</label>
            <p class="mt-1 text-base sm:text-lg font-medium capitalize">{{ sms.type || 'Plain' }}</p>
          </div>

          <div>
            <label class="text-xs sm:text-sm font-medium text-gray-500">Character Count</label>
            <p class="mt-1 text-base sm:text-lg font-medium">{{ sms.message?.length || 0 }}</p>
          </div>
        </div>

        <div v-if="sms.error_message" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 sm:p-4">
          <h4 class="text-sm sm:text-base font-medium text-red-800 dark:text-red-300 mb-2">Error Message</h4>
          <p class="text-sm text-red-600 dark:text-red-400">{{ sms.error_message }}</p>
        </div>

        <div v-if="sms.status === 'failed'" class="flex gap-2">
          <button @click="retrySms" class="btn btn-primary w-full sm:w-auto">
            <RefreshCw :size="16" />
            Retry SMS
          </button>
        </div>
      </div>

      <div class="space-y-4 sm:space-y-6">
        <!-- Timeline -->
        <div class="card p-4 sm:p-6">
          <h3 class="text-base sm:text-lg font-semibold mb-4 flex items-center gap-2">
            <Clock :size="18" class="sm:w-5 sm:h-5" />
            Timeline
          </h3>
          <div class="space-y-3 sm:space-y-4">
            <div class="flex gap-3">
              <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">Created</p>
                <p class="text-xs text-gray-500">{{ formatDate(sms.created_at) }}</p>
              </div>
            </div>

            <div v-if="sms.sent_at" class="flex gap-3">
              <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">Sent</p>
                <p class="text-xs text-gray-500">{{ formatDate(sms.sent_at) }}</p>
              </div>
            </div>

            <div v-if="sms.delivered_at" class="flex gap-3">
              <div class="w-2 h-2 rounded-full bg-green-600 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">Delivered</p>
                <p class="text-xs text-gray-500">{{ formatDate(sms.delivered_at) }}</p>
              </div>
            </div>

            <div v-if="sms.failed_at" class="flex gap-3">
              <div class="w-2 h-2 rounded-full bg-red-500 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">Failed</p>
                <p class="text-xs text-gray-500">{{ formatDate(sms.failed_at) }}</p>
              </div>
            </div>

            <div v-if="sms.retried_at" class="flex gap-3">
              <div class="w-2 h-2 rounded-full bg-yellow-500 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">Retried</p>
                <p class="text-xs text-gray-500">{{ formatDate(sms.retried_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Raw Data -->
        <div class="card p-6">
          <h3 class="text-lg font-semibold mb-4">Raw Data</h3>
          <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
            <pre class="text-xs">{{ JSON.stringify(sms, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>
      </div>

      <div v-else class="card p-12 text-center">
        <p class="text-gray-500">SMS not found</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, Phone, Clock, RefreshCw, CheckCircle, XCircle, Send } from 'lucide-vue-next';
import { smsDetails, smsRetry } from '../services/api';
import { format } from 'date-fns';

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const sms = ref(null);

async function loadData() {
  loading.value = true;
  try {
    const response = await smsDetails(route.params.id);
    sms.value = response.data;
  } catch (error) {
    console.error('Failed to load SMS details:', error);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push('/sms');
}

async function retrySms() {
  if (!confirm('Are you sure you want to retry sending this SMS?')) return;

  try {
    await smsRetry(sms.value.id);
    alert('SMS retry initiated successfully');
    loadData();
  } catch (error) {
    alert('Failed to retry SMS: ' + error.message);
  }
}

function getStatusColor(status) {
  const colors = {
    delivered: 'success',
    sent: 'info',
    failed: 'danger',
    pending: 'warning'
  };
  return colors[status] || 'info';
}

function getStatusIcon(status) {
  const icons = {
    delivered: CheckCircle,
    sent: Send,
    failed: XCircle,
    pending: Clock
  };
  return icons[status] || Clock;
}

function formatDate(date) {
  if (!date) return '';
  return format(new Date(date), 'PPpp');
}

onMounted(() => {
  loadData();
});
</script>
