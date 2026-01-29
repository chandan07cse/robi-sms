<template>
  <div class="page-container">
    <div class="page-content space-y-6">
    <!-- Filters -->
    <div class="card p-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium mb-2">Status</label>
          <select v-model="filters.status" class="input w-full">
            <option value="">All Status</option>
            <option value="delivered">Delivered</option>
            <option value="sent">Sent</option>
            <option value="failed">Failed</option>
            <option value="pending">Pending</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Phone Number</label>
          <input v-model="filters.phone" type="text" placeholder="Search phone..." class="input w-full">
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Start Date</label>
          <input v-model="filters.start_date" type="date" class="input w-full">
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">End Date</label>
          <input v-model="filters.end_date" type="date" class="input w-full">
        </div>

        <div class="flex items-end gap-2">
          <button @click="applyFilters" class="btn btn-primary flex-1">
            <Filter :size="16" />
            Filter
          </button>
          <button @click="resetFilters" class="btn btn-secondary">
            <X :size="16" />
          </button>
          <button @click="exportData" class="btn btn-secondary">
            <Download :size="16" />
          </button>
        </div>
      </div>
    </div>

    <!-- SMS List -->
    <div class="card">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-bold">SMS Messages</h2>
          <span class="text-sm text-gray-500">Total: {{ pagination.total }}</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Phone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sender</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Message</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
              <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-48"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div></td>
            </tr>

            <tr v-else v-for="sms in smsList" :key="sms.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
              <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ sms.id.substring(0, 8) }}</td>
              <td class="px-6 py-4 text-sm font-medium">{{ sms.phone }}</td>
              <td class="px-6 py-4 text-sm">{{ sms.sender }}</td>
              <td class="px-6 py-4 text-sm max-w-md truncate">{{ sms.message }}</td>
              <td class="px-6 py-4 text-sm">
                <span :class="['badge', `badge-${getStatusColor(sms.status)}`]">
                  <component :is="getStatusIcon(sms.status)" :size="12" />
                  {{ sms.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(sms.created_at) }}</td>
              <td class="px-6 py-4 text-sm">
                <div class="flex items-center gap-2">
                  <button @click="viewDetails(sms.id)" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                    <Eye :size="16" />
                  </button>
                  <button v-if="sms.status === 'failed'" @click="retrySms(sms.id)" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded text-blue-600">
                    <RefreshCw :size="16" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && smsList.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <Inbox :size="48" class="mx-auto mb-2 opacity-50" />
                <p>No SMS messages found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="p-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
            {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of 
            {{ pagination.total }} results
          </p>
          <div class="flex items-center gap-2">
            <button 
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="btn btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronLeft :size="16" />
            </button>
            <span class="px-4 py-2">Page {{ pagination.current_page }}</span>
            <button 
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page * pagination.per_page >= pagination.total"
              class="btn btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronRight :size="16" />
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { Filter, X, Download, Eye, RefreshCw, CheckCircle, XCircle, Clock, Send, Inbox, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { smsList as fetchSmsList, smsRetry, exportSms } from '../services/api';
import { formatDistanceToNow } from 'date-fns';

const router = useRouter();
const loading = ref(false);
const smsList = ref([]);
const pagination = ref({
  current_page: 1,
  per_page: 50,
  total: 0
});

const filters = ref({
  status: '',
  phone: '',
  sender: '',
  start_date: '',
  end_date: ''
});

async function loadData(page = 1) {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== '')
      )
    };

    const response = await fetchSmsList(params);
    smsList.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      per_page: response.data.per_page,
      total: response.data.total
    };
  } catch (error) {
    console.error('Failed to load SMS list:', error);
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  loadData(1);
}

function resetFilters() {
  filters.value = {
    status: '',
    phone: '',
    sender: '',
    start_date: '',
    end_date: ''
  };
  loadData(1);
}

async function exportData() {
  try {
    const params = Object.fromEntries(
      Object.entries(filters.value).filter(([_, v]) => v !== '')
    );
    params.format = 'csv';

    const response = await exportSms(params);
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `sms-export-${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Failed to export data:', error);
  }
}

function changePage(page) {
  loadData(page);
}

function viewDetails(id) {
  router.push(`/sms/${id}`);
}

async function retrySms(id) {
  if (!confirm('Are you sure you want to retry sending this SMS?')) return;

  try {
    await smsRetry(id);
    alert('SMS retry initiated successfully');
    loadData(pagination.value.current_page);
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
  return formatDistanceToNow(new Date(date), { addSuffix: true });
}

onMounted(() => {
  loadData();
  
  // Auto-refresh every 10 seconds
  const interval = setInterval(() => loadData(pagination.value.current_page), 10000);
  
  return () => clearInterval(interval);
});
</script>
