<template>
  <div class="page-container">
    <div class="page-content space-y-4 sm:space-y-6">
      <!-- Header Section (matching other pages) -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2 sm:gap-3">
            <div class="p-2 sm:p-3 bg-primary-100 dark:bg-primary-900/30 rounded-xl">
              <Send :size="24" class="sm:w-7 sm:h-7 text-primary-600 dark:text-primary-400" />
            </div>
            Send SMS
          </h1>
          <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2">
            Compose and send SMS messages to single or multiple recipients
          </p>
        </div>
        <div class="text-left sm:text-right">
          <p class="text-xs sm:text-sm text-gray-500">Messages sent today</p>
          <p class="text-xl sm:text-2xl font-bold text-primary-600">{{ todayCount }}</p>
        </div>
      </div>

      <!-- Send SMS Form -->
      <div class="card p-4 sm:p-6">
        <form @submit.prevent="sendSms" class="space-y-4 sm:space-y-6">
          <!-- Phone Numbers Section -->
          <div>
            <label class="text-sm font-medium mb-2 flex items-center gap-2">
              <Phone :size="16" />
              Phone Number(s) *
            </label>
            <textarea 
              v-model="form.phone" 
              class="input w-full" 
              rows="5"
              placeholder="Enter phone numbers (comma-separated for bulk)&#10;Examples:&#10;  • Single: 01712345678&#10;  • Multiple: 01712345678, 01887654321, 01965432100"
              required
            ></textarea>
            <div class="flex items-center justify-between mt-2">
              <p class="text-xs text-gray-500">
                Format: 01XXXXXXXXX (Bangladesh mobile numbers)
              </p>
              <p class="text-xs font-medium text-primary-600">
                {{ phoneCount }} recipient(s)
              </p>
            </div>
          </div>

          <!-- Message Section -->
          <div>
            <label class="text-sm font-medium mb-2 flex items-center gap-2">
              <MessageSquare :size="16" />
              Message Content *
            </label>
            <textarea 
              v-model="form.message" 
              class="input w-full" 
              rows="6"
              placeholder="Type your message here...&#10;&#10;Tips:&#10;  • Keep it concise and clear&#10;  • Plain text supports 160 chars per SMS&#10;  • Unicode (Bangla) supports 70 chars per SMS"
              required
              @input="updateCharCount"
            ></textarea>
            <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-4">
                  <span class="text-gray-600 dark:text-gray-400">
                    <Hash :size="14" class="inline mr-1" />
                    {{ charCount }} characters
                  </span>
                  <span class="text-gray-600 dark:text-gray-400">
                    {{ smsCount }} SMS part(s)
                  </span>
                  <span :class="['font-medium', charCount > maxChars ? 'text-red-600' : 'text-green-600']">
                    {{ charCount > maxChars ? 'Too long!' : 'Good' }}
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <label class="text-xs text-gray-500">Type:</label>
                  <select v-model="form.type" class="input input-sm">
                    <option value="plain">Plain Text</option>
                    <option value="unicode">Unicode (বাংলা)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Cost Estimate -->
          <div class="p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="text-sm sm:text-base font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
              <Sparkles :size="16" />
              Estimated Cost
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 text-sm">
              <div>
                <p class="text-xs sm:text-sm text-blue-600 dark:text-blue-400">Recipients</p>
                <p class="text-base sm:text-lg font-bold text-blue-900 dark:text-blue-100">{{ phoneCount }}</p>
              </div>
              <div>
                <p class="text-xs sm:text-sm text-blue-600 dark:text-blue-400">SMS Parts</p>
                <p class="text-base sm:text-lg font-bold text-blue-900 dark:text-blue-100">{{ smsCount }}</p>
              </div>
              <div>
                <p class="text-xs sm:text-sm text-blue-600 dark:text-blue-400">Total Messages</p>
                <p class="text-base sm:text-lg font-bold text-blue-900 dark:text-blue-100">{{ phoneCount * smsCount }}</p>
              </div>
              <div>
                <p class="text-xs sm:text-sm text-blue-600 dark:text-blue-400">Est. Cost</p>
                <p class="text-base sm:text-lg font-bold text-blue-900 dark:text-blue-100">
                  ~{{ (phoneCount * smsCount * 0.5).toFixed(2) }} BDT
                </p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
            <button 
              type="submit" 
              :disabled="sending || !isFormValid"
              class="btn btn-primary btn-lg w-full sm:w-auto"
            >
              <Send :size="20" />
              {{ sending ? 'Sending...' : 'Send SMS Now' }}
            </button>
            <button 
              type="button" 
              @click="resetForm"
              class="btn btn-secondary btn-lg w-full sm:w-auto"
              :disabled="sending"
            >
              <X :size="20" />
              Clear Form
            </button>
          </div>

          <!-- Status Message -->
          <div v-if="statusMessage" 
            class="p-4 rounded-lg"
            :class="{
              'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200': statusMessage.success,
              'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200': !statusMessage.success
            }"
          >
            <p class="font-medium">{{ statusMessage.message }}</p>
            <div v-if="statusMessage.results" class="mt-2 text-sm">
              <p>✅ Sent: {{ statusMessage.results.sent }}</p>
              <p v-if="statusMessage.results.failed > 0">❌ Failed: {{ statusMessage.results.failed }}</p>
            </div>
          </div>
        </form>
      </div>

      <!-- Recent Sent SMS -->
      <div class="card p-6" v-if="recentSms.length > 0">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <Clock :size="20" />
          Recently Sent (Last 10)
        </h3>
        <div class="space-y-3">
          <div 
            v-for="sms in recentSms" 
            :key="sms.id"
            class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <div :class="['w-3 h-3 rounded-full mt-1.5 flex-shrink-0', getStatusColor(sms.status)]"></div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2 mb-1">
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ sms.phone }}</p>
                <span :class="['badge badge-sm', `badge-${getStatusBadgeColor(sms.status)}`]">
                  {{ sms.status }}
                </span>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ sms.message }}</p>
              <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                <span>{{ formatDate(sms.created_at) }}</span>
                <span>•</span>
                <span>{{ sms.type === 'unicode' ? 'Unicode' : 'Plain' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Tips -->
      <div class="card p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-2 border-purple-200 dark:border-purple-800">
        <h3 class="text-lg font-semibold mb-3 flex items-center gap-2 text-purple-900 dark:text-purple-100">
          <Sparkles :size="20" />
          Quick Tips
        </h3>
        <ul class="space-y-2 text-sm text-purple-800 dark:text-purple-200">
          <li class="flex items-start gap-2">
            <span class="text-purple-600 dark:text-purple-400 mt-0.5">•</span>
            <span>Sender ID is configured in Settings and applied automatically</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-purple-600 dark:text-purple-400 mt-0.5">•</span>
            <span>Plain text: 160 characters per SMS, Unicode (Bangla): 70 characters per SMS</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-purple-600 dark:text-purple-400 mt-0.5">•</span>
            <span>For bulk sending, separate phone numbers with commas</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-purple-600 dark:text-purple-400 mt-0.5">•</span>
            <span>Avoid special characters that might not display correctly on all devices</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Send, X, Phone, MessageSquare, Hash, Sparkles, Clock } from 'lucide-vue-next';
import api from '../services/api';
import { formatDistanceToNow } from 'date-fns';

const sending = ref(false);
const recentSms = ref([]);
const statusMessage = ref(null);
const todayCount = ref(0);

const form = ref({
  sender: '',
  phone: '',
  message: '',
  type: 'plain'
});

// Load default sender and today's count
onMounted(async () => {
  try {
    const response = await api.get('/settings');
    if (response.data.success && response.data.settings.default_sender) {
      form.value.sender = response.data.settings.default_sender.value;
    }
  } catch (error) {
    console.error('Failed to load default sender:', error);
  }
  
  // Load today's sent count
  loadTodayCount();
});

const loadTodayCount = async () => {
  try {
    const today = new Date().toISOString().split('T')[0];
    const response = await api.get('/stats', {
      params: { start_date: today, end_date: today }
    });
    todayCount.value = response.data.total || 0;
  } catch (error) {
    console.error('Failed to load today count:', error);
  }
};

const charCount = computed(() => form.value.message.length);
const maxChars = computed(() => form.value.type === 'unicode' ? 500 : 1000);
const smsCount = computed(() => {
  const length = form.value.message.length;
  if (length === 0) return 0;
  if (form.value.type === 'unicode') {
    return Math.ceil(length / 70);
  }
  return Math.ceil(length / 160);
});

const phoneCount = computed(() => {
  if (!form.value.phone) return 0;
  return form.value.phone.split(',').filter(p => p.trim().length > 0).length;
});

const isFormValid = computed(() => {
  return form.value.phone && form.value.message && phoneCount.value > 0;
});

function updateCharCount() {
  // Trigger reactivity
}

async function sendSms() {
  if (!form.value.sender) {
    statusMessage.value = {
      success: false,
      message: 'Sender ID not configured. Please configure it in Settings first.'
    };
    return;
  }

  if (!isFormValid.value) {
    statusMessage.value = {
      success: false,
      message: 'Please fill in all required fields correctly.'
    };
    return;
  }

  sending.value = true;
  statusMessage.value = null;

  try {
    const phones = form.value.phone.split(',').map(p => p.trim()).filter(p => p);
    
    const response = await api.post('/send-sms', {
      sender: form.value.sender,
      phone: phones,
      message: form.value.message,
      type: form.value.type
    });

    if (response.data.success) {
      statusMessage.value = {
        success: true,
        message: `SMS sent successfully to ${response.data.sent} recipient(s)!`,
        results: {
          sent: response.data.sent,
          failed: response.data.failed
        }
      };

      // Add to recent list
      phones.forEach(phone => {
        recentSms.value.unshift({
          id: Date.now() + Math.random(),
          phone,
          message: form.value.message,
          status: 'sent',
          type: form.value.type,
          created_at: new Date().toISOString()
        });
      });

      // Keep only last 10
      if (recentSms.value.length > 10) {
        recentSms.value = recentSms.value.slice(0, 10);
      }

      // Reset form (but keep sender)
      const senderBackup = form.value.sender;
      form.value = {
        sender: senderBackup,
        phone: '',
        message: '',
        type: 'plain'
      };

      // Update today count
      loadTodayCount();
    } else {
      statusMessage.value = {
        success: false,
        message: response.data.message || 'Failed to send SMS'
      };
    }
  } catch (error) {
    statusMessage.value = {
      success: false,
      message: error.response?.data?.message || 'An error occurred while sending SMS'
    };
  } finally {
    sending.value = false;
  }
}

function resetForm() {
  const senderBackup = form.value.sender;
  form.value = {
    sender: senderBackup, // Keep sender ID
    phone: '',
    message: '',
    type: 'plain'
  };
  statusMessage.value = null;
}

function getStatusColor(status) {
  const colors = {
    sent: 'bg-blue-500',
    delivered: 'bg-green-500',
    failed: 'bg-red-500',
    pending: 'bg-yellow-500'
  };
  return colors[status] || 'bg-gray-500';
}

function getStatusBadgeColor(status) {
  const colors = {
    sent: 'info',
    delivered: 'success',
    failed: 'danger',
    pending: 'warning'
  };
  return colors[status] || 'info';
}

function formatDate(date) {
  if (!date) return '';
  return formatDistanceToNow(new Date(date), { addSuffix: true });
}
</script>

