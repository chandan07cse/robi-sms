import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import '../css/app.css';

// Import pages
import Dashboard from './pages/Dashboard.vue';
import SmsList from './pages/SmsList.vue';
import SmsDetails from './pages/SmsDetails.vue';
import SendSms from './pages/SendSms.vue';
import Analytics from './pages/Analytics.vue';
import Settings from './pages/Settings.vue';

// Create router
const router = createRouter({
  history: createWebHistory(window.DASHBOARD_CONFIG.dashboardPath || '/sms-dashboard'),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: Dashboard
    },
    {
      path: '/send',
      name: 'send-sms',
      component: SendSms
    },
    {
      path: '/sms',
      name: 'sms-list',
      component: SmsList
    },
    {
      path: '/sms/:id',
      name: 'sms-details',
      component: SmsDetails
    },
    {
      path: '/analytics',
      name: 'analytics',
      component: Analytics
    },
    {
      path: '/settings',
      name: 'settings',
      component: Settings
    }
  ]
});

// Create app
const app = createApp(App);
app.use(router);
app.mount('#app');
