<template>
  <div class="page-container">
    <div class="page-content space-y-6">
      <!-- <h2 class="text-2xl font-bold">Analytics</h2> -->

      <!-- Period Selector -->
      <div class="card p-4">
      <div class="flex items-center gap-4">
        <label class="font-medium">Period:</label>
        <div class="flex gap-2">
          <button
            v-for="period in periods"
            :key="period.value"
            @click="selectedPeriod = period.value"
            :class="['px-4 py-2 rounded-lg transition-all', selectedPeriod === period.value ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
          >
            {{ period.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-500">Success Rate</h3>
          <TrendingUp :size="20" class="text-green-500" />
        </div>
        <p class="text-3xl font-bold text-green-600">{{ successRate }}%</p>
        <p class="text-xs text-gray-500 mt-1">Delivery success</p>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-500">Failure Rate</h3>
          <TrendingDown :size="20" class="text-red-500" />
        </div>
        <p class="text-3xl font-bold text-red-600">{{ failureRate }}%</p>
        <p class="text-xs text-gray-500 mt-1">Failed deliveries</p>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-500">Avg Response Time</h3>
          <Clock :size="20" class="text-blue-500" />
        </div>
        <p class="text-3xl font-bold text-blue-600">2.3s</p>
        <p class="text-xs text-gray-500 mt-1">API response</p>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-500">Peak Hour</h3>
          <Activity :size="20" class="text-purple-500" />
        </div>
        <p class="text-3xl font-bold text-purple-600">14:00</p>
        <p class="text-xs text-gray-500 mt-1">Highest traffic</p>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Volume Trend -->
      <div class="card p-6">
        <h3 class="text-lg font-semibold mb-4">SMS Volume Trend</h3>
        <div class="h-80">
          <Line :data="volumeTrendData" :options="lineOptions" />
        </div>
      </div>

      <!-- Status Distribution -->
      <div class="card p-6">
        <h3 class="text-lg font-semibold mb-4">Status Distribution</h3>
        <div class="h-80">
          <Doughnut :data="statusDistribution" :options="doughnutOptions" />
        </div>
      </div>

      <!-- Hourly Activity -->
      <div class="card p-6">
        <h3 class="text-lg font-semibold mb-4">Hourly Activity</h3>
        <div class="h-80">
          <Bar :data="hourlyActivity" :options="barOptions" />
        </div>
      </div>

      <!-- Top Senders -->
      <div class="card p-6">
        <h3 class="text-lg font-semibold mb-4">Top Sender IDs</h3>
        <div class="space-y-4">
          <div v-for="(sender, index) in topSenders" :key="index" class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center font-bold text-primary-600">
              {{ index + 1 }}
            </div>
            <div class="flex-1">
              <p class="font-medium">{{ sender.name }}</p>
              <p class="text-sm text-gray-500">{{ sender.count }} SMS</p>
            </div>
            <div class="w-32">
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div 
                  class="bg-primary-600 h-2 rounded-full transition-all"
                  :style="{ width: (sender.count / topSenders[0].count * 100) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delivery Time Analysis -->
      <div class="card p-6 lg:col-span-2">
        <h3 class="text-lg font-semibold mb-4">Delivery Time Analysis</h3>
        <div class="h-80">
          <Line :data="deliveryTimeData" :options="lineOptions" />
        </div>
      </div>
    </div>

    <!-- Detailed Stats Table -->
    <div class="card">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold">Daily Breakdown</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sent</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Delivered</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Failed</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Success Rate</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="day in dailyStats" :key="day.date" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
              <td class="px-6 py-4 text-sm font-medium">{{ formatDate(day.date) }}</td>
              <td class="px-6 py-4 text-sm text-right">{{ day.total }}</td>
              <td class="px-6 py-4 text-sm text-right">{{ day.sent }}</td>
              <td class="px-6 py-4 text-sm text-right text-green-600">{{ day.delivered }}</td>
              <td class="px-6 py-4 text-sm text-right text-red-600">{{ day.failed }}</td>
              <td class="px-6 py-4 text-sm text-right font-medium">
                {{ day.total > 0 ? ((day.delivered / day.total) * 100).toFixed(1) : 0 }}%
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { TrendingUp, TrendingDown, Clock, Activity } from 'lucide-vue-next';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import { getStats } from '../services/api';
import { format } from 'date-fns';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend);

const periods = [
  { label: 'Today', value: 'today' },
  { label: '7 Days', value: '7days' },
  { label: '30 Days', value: '30days' },
  { label: '90 Days', value: '90days' }
];

const selectedPeriod = ref('30days');
const stats = ref(null);
const loading = ref(false);

const successRate = computed(() => {
  if (!stats.value || stats.value.total === 0) return 0;
  return ((stats.value.delivered / stats.value.total) * 100).toFixed(1);
});

const failureRate = computed(() => {
  if (!stats.value || stats.value.total === 0) return 0;
  return ((stats.value.failed / stats.value.total) * 100).toFixed(1);
});

const dailyStats = computed(() => stats.value?.daily || []);

const volumeTrendData = computed(() => ({
  labels: dailyStats.value.map(d => format(new Date(d.date), 'MMM dd')),
  datasets: [
    {
      label: 'Total SMS',
      data: dailyStats.value.map(d => d.total),
      borderColor: '#8b5cf6',
      backgroundColor: 'rgba(139, 92, 246, 0.1)',
      tension: 0.4,
      fill: true
    }
  ]
}));

const statusDistribution = computed(() => ({
  labels: ['Delivered', 'Sent', 'Failed', 'Pending'],
  datasets: [{
    data: [
      stats.value?.delivered || 0,
      stats.value?.sent || 0,
      stats.value?.failed || 0,
      stats.value?.pending || 0
    ],
    backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#f59e0b'],
    borderWidth: 0
  }]
}));

const hourlyActivity = computed(() => ({
  labels: Array.from({ length: 24 }, (_, i) => `${i}:00`),
  datasets: [{
    label: 'SMS Count',
    data: Array.from({ length: 24 }, () => Math.floor(Math.random() * 100)),
    backgroundColor: '#8b5cf6'
  }]
}));

const deliveryTimeData = computed(() => ({
  labels: dailyStats.value.map(d => format(new Date(d.date), 'MMM dd')),
  datasets: [{
    label: 'Avg Delivery Time (seconds)',
    data: dailyStats.value.map(() => (Math.random() * 5 + 1).toFixed(2)),
    borderColor: '#10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    tension: 0.4
  }]
}));

const topSenders = ref([
  { name: 'COMPANY', count: 1250 },
  { name: 'ALERT', count: 890 },
  { name: 'NOTICE', count: 645 },
  { name: 'INFO', count: 432 },
  { name: 'UPDATE', count: 321 }
]);

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top'
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
};

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
};

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
};

async function loadStats() {
  loading.value = true;
  try {
    const startDate = getStartDate(selectedPeriod.value);
    const endDate = format(new Date(), 'yyyy-MM-dd');
    
    const response = await getStats({ start_date: startDate, end_date: endDate });
    stats.value = response.data;
  } catch (error) {
    console.error('Failed to load stats:', error);
  } finally {
    loading.value = false;
  }
}

function getStartDate(period) {
  const now = new Date();
  switch (period) {
    case 'today':
      return format(now, 'yyyy-MM-dd');
    case '7days':
      return format(new Date(now.setDate(now.getDate() - 7)), 'yyyy-MM-dd');
    case '30days':
      return format(new Date(now.setDate(now.getDate() - 30)), 'yyyy-MM-dd');
    case '90days':
      return format(new Date(now.setDate(now.getDate() - 90)), 'yyyy-MM-dd');
    default:
      return format(new Date(now.setDate(now.getDate() - 30)), 'yyyy-MM-dd');
  }
}

function formatDate(dateStr) {
  return format(new Date(dateStr), 'MMM dd, yyyy');
}

watch(selectedPeriod, () => {
  loadStats();
});

onMounted(() => {
  loadStats();
});
</script>
