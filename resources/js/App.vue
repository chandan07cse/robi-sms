<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-gray-900 dark:to-gray-800 w-full overflow-x-hidden">
    <!-- Mobile Header -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
      <div class="flex items-center justify-between">
        <button @click="toggleSidebar" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
          <Menu :size="24" />
        </button>
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg flex items-center justify-center">
            <MessageSquare :size="18" class="text-white" />
          </div>
          <h1 class="text-lg font-bold">SMS Dashboard</h1>
        </div>
        <button @click="toggleTheme" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
          <Sun v-if="theme === 'dark'" :size="20" />
          <Moon v-else :size="20" />
        </button>
      </div>
    </div>

    <!-- Mobile Overlay -->
    <div 
      v-if="sidebarOpen" 
      @click="toggleSidebar"
      class="lg:hidden fixed inset-0 bg-black/50 z-40"
    ></div>

    <!-- Sidebar -->
    <aside :class="['sidebar', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg flex items-center justify-center">
              <MessageSquare :size="24" class="text-white" />
            </div>
            <div>
              <h1 class="text-xl font-bold">SMS Dashboard</h1>
              <p class="text-xs text-gray-500 dark:text-gray-400">AdaReach API</p>
            </div>
          </div>
          <button @click="toggleSidebar" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <Menu :size="20" />
          </button>
        </div>
      </div>

      <nav class="p-4 flex-1">
        <router-link
          v-for="item in menuItems"
          :key="item.name"
          :to="item.path"
          :class="['nav-item', $route.path === item.path && 'active']"
          @click="closeSidebarOnMobile"
        >
          <component :is="item.icon" :size="20" />
          <span>{{ item.label }}</span>
          <span v-if="item.badge" class="ml-auto badge badge-danger">{{ item.badge }}</span>
        </router-link>
        
        <!-- Logout Button -->
        <button @click="logout" class="nav-item text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 mt-2 w-full">
          <LogOut :size="20" />
          <span>Logout</span>
        </button>
      </nav>

      <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
          <p>© 2026 AdaReach SMS</p>
          <p>Developed by:</p>
          <p class="font-medium text-primary-600 dark:text-primary-400">{{ author }}</p>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <div class="main-content">
      <!-- Page content -->
      <main class="p-3 sm:p-4 md:p-6 min-h-screen max-h-screen overflow-y-auto pt-16 lg:pt-6">
        <router-view :key="$route.fullPath" @update="handleUpdate" />
      </main>
    </div>

    <!-- Toast notifications -->
    <div class="toast-container">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="['toast', `toast-${toast.type}`]"
      >
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  LayoutDashboard,
  MessageSquare,
  BarChart3,
  Settings,
  Menu,
  Bell,
  Sun,
  Moon,
  Send,
  LogOut
} from 'lucide-vue-next';
import { initializeEcho, disconnectEcho } from './services/echo';

const route = useRoute();
const sidebarOpen = ref(true);
const theme = ref('dark');
const connected = ref(false);
const notifications = ref(0);
const toasts = ref([]);
const author = ref(window.DASHBOARD_CONFIG.author);

const menuItems = [
  { name: 'dashboard', path: '/', label: 'Dashboard', icon: LayoutDashboard },
  { name: 'send', path: '/send', label: 'Send SMS', icon: MessageSquare },
  { name: 'sms', path: '/sms', label: 'SMS Messages', icon: MessageSquare },
  { name: 'analytics', path: '/analytics', label: 'Analytics', icon: BarChart3 },
  { name: 'settings', path: '/settings', label: 'Settings', icon: Settings },
];

const pageTitle = computed(() => {
  const item = menuItems.find(m => m.path === route.path);
  return item?.label || 'SMS Dashboard';
});

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value;
}

function closeSidebarOnMobile() {
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false;
  }
}

function toggleTheme() {
  theme.value = theme.value === 'light' ? 'dark' : 'light';
  document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', theme.value);
}

function showToast(message, type = 'info') {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  }, 5000);
}

function logout() {
  if (confirm('Are you sure you want to logout?')) {
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/${window.DASHBOARD_CONFIG.path}/logout`;
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
  }
}

function handleUpdate(data) {
  showToast(data.message, data.type);
}

onMounted(() => {
  // Initialize theme
  const savedTheme = localStorage.getItem('theme') || 'dark';
  theme.value = savedTheme;
  if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark');
  }

  // Initialize Echo for real-time updates only if enabled
  const realtimeEnabled = localStorage.getItem('realtimeUpdates') === 'true';
  
  if (realtimeEnabled) {
    try {
      const echo = initializeEcho();
      
      echo.channel('sms-dashboard')
        .listen('.sms.sent', (data) => {
          connected.value = true;
          showToast(`SMS sent to ${data.data.phone}`, 'success');
        })
        .listen('.sms.failed', (data) => {
          connected.value = true;
          showToast(`SMS failed to ${data.data.phone}`, 'error');
          notifications.value++;
        })
        .listen('.sms.delivered', (data) => {
          connected.value = true;
          showToast(`SMS delivered to ${data.data.phone}`, 'success');
        });

      connected.value = true;
    } catch (error) {
      console.warn('WebSocket connection failed:', error.message);
      connected.value = false;
    }
  } else {
    console.info('Real-time updates are disabled. Enable in Settings to use WebSocket features.');
    connected.value = false;
  }

  // Responsive sidebar
  const handleResize = () => {
    if (window.innerWidth < 1024) {
      sidebarOpen.value = false;
    } else {
      sidebarOpen.value = true;
    }
  };
  
  handleResize();
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  disconnectEcho();
});
</script>

<style scoped>
.sidebar {
  @apply fixed left-0 top-0 h-screen w-64 bg-white dark:bg-gray-800 shadow-xl z-50 flex flex-col transition-transform duration-300 border-r border-gray-200 dark:border-gray-700;
}

.main-content {
  @apply min-h-screen transition-all duration-300;
}

@media (min-width: 1024px) {
  .sidebar {
    @apply translate-x-0;
  }
  
  .main-content {
    @apply ml-64;
  }
}

.nav-item {
  @apply flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 mb-1;
}

.nav-item.active {
  @apply bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 font-medium;
}
</style>
