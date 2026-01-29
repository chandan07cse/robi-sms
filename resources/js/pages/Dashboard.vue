<template>
  <div class="page-container">
    <div class="page-content space-y-6">
      <!-- <h2 class="text-2xl font-bold flex items-center gap-2">
        <LayoutDashboard :size="24" />
        Dashboard
      </h2> -->
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard
        v-for="stat in stats"
        :key="stat.label"
        :label="stat.label"
        :value="stat.value"
        :change="stat.change"
        :icon="stat.icon"
        :color="stat.color"
        :loading="loading"
      />
    </div>

    <!-- Credits -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold flex items-center gap-2">
            <Wallet :size="20" class="text-blue-500" />
            GUI Balance
          </h3>
          <button @click="refreshCredits" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <RefreshCw :size="16" :class="refreshing && 'animate-spin'" />
          </button>
        </div>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ credits.gui }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">SMS credits</p>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold flex items-center gap-2">
            <Cpu :size="20" class="text-purple-500" />
            API Balance
          </h3>
          <button @click="refreshCredits" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <RefreshCw :size="16" :class="refreshing && 'animate-spin'" />
          </button>
        </div>
        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ credits.api }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">SMS credits</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Status Distribution -->
      <div class="card p-6">
        <h3 class="text-lg font-semibold mb-4">Status Distribution</h3>
        <div class="h-64">
          <Doughnut :data="statusChartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Daily Trend -->
      <div class="card p-6">
        <h3 class="text-lg font-semibold mb-4">Daily Trend (Last 7 Days)</h3>
        <div class="h-64">
          <Line :data="dailyChartData" :options="lineChartOptions" />
        </div>
      </div>
    </div>

    <!-- Recent SMS -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Recent SMS</h3>
        <router-link to="/sms" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
          View All →
        </router-link>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Phone</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Message</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="sms in recentSms" :key="sms.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
              <td class="px-4 py-3 text-sm">{{ sms.phone }}</td>
              <td class="px-4 py-3 text-sm">{{ truncate(sms.message, 50) }}</td>
              <td class="px-4 py-3 text-sm">
                <span :class="['badge', `badge-${getStatusColor(sms.status)}`]">
                  {{ sms.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(sms.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Wallet, Cpu, RefreshCw, TrendingUp, TrendingDown, Send, CheckCircle, XCircle, Clock, LayoutDashboard } from 'lucide-vue-next';
import { Chart as ChartJS, ArcElement, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';
import { Doughnut, Line } from 'vue-chartjs';
import { getOverview, getCredits, smsList } from '../services/api';
import StatCard from '../components/StatCard.vue';
import { formatDistanceToNow } from 'date-fns';

ChartJS.register(ArcElement, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const loading = ref(true);
const refreshing = ref(false);
const overview = ref(null);
const credits = ref({ gui: 0, api: 0 });
const recentSms = ref([]);

const stats = computed(() => [
  {
    label: 'Total SMS',
    value: overview.value?.stats?.total || 0,
    change: '+12%',
    icon: Send,
    color: 'blue'
  },
  {
    label: 'Delivered',
    value: overview.value?.stats?.delivered || 0,
    change: `${overview.value?.success_rate || 0}%`,
    icon: CheckCircle,
    color: 'green'
  },
  {
    label: 'Failed',
    value: overview.value?.stats?.failed || 0,
    change: `${overview.value?.failure_rate || 0}%`,
    icon: XCircle,
    color: 'red'
  },
  {
    label: 'Pending',
    value: overview.value?.stats?.pending || 0,
    change: '',
    icon: Clock,
    color: 'yellow'
  }
]);

const statusChartData = computed(() => ({
  labels: overview.value?.status_distribution?.map(s => s.name) || [],
  datasets: [{
    data: overview.value?.status_distribution?.map(s => s.value) || [],
    backgroundColor: overview.value?.status_distribution?.map(s => s.color) || [],
    borderWidth: 0
  }]
}));

const dailyChartData = computed(() => {
  const daily = overview.value?.stats?.daily?.slice(-7) || [];
  return {
    labels: daily.map(d => d.date),
    datasets: [
      {
        label: 'Sent',
        data: daily.map(d => d.sent),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4
      },
      {
        label: 'Delivered',
        data: daily.map(d => d.delivered),
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4
      },
      {
        label: 'Failed',
        data: daily.map(d => d.failed),
        borderColor: '#ef4444',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        tension: 0.4
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
};

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
};

async function loadData() {
  loading.value = true;
  try {
    const [overviewRes, creditsRes, smsRes] = await Promise.all([
      getOverview({ period: '7days' }),
      getCredits(),
      smsList({ per_page: 10 })
    ]);

    overview.value = overviewRes.data;
    credits.value = creditsRes.data;
    recentSms.value = smsRes.data.data;
  } catch (error) {
    console.error('Failed to load dashboard data:', error);
  } finally {
    loading.value = false;
  }
}

async function refreshCredits() {
  refreshing.value = true;
  try {
    const res = await getCredits();
    credits.value = res.data;
  } catch (error) {
    console.error('Failed to refresh credits:', error);
  } finally {
    refreshing.value = false;
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

function truncate(text, length) {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
}

function formatDate(date) {
  if (!date) return '';
  return formatDistanceToNow(new Date(date), { addSuffix: true });
}

onMounted(() => {
  loadData();
  
  // Refresh data every 30 seconds
  const interval = setInterval(loadData, 30000);
  
  return () => clearInterval(interval);
});
</script>
