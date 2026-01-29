<template>
  <div class="card p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">{{ label }}</h2>
        <p v-if="change" :class="['text-sm mt-1', changeColor]">
          <component :is="changeIcon" :size="14" class="inline" />
          {{ change }}
        </p>
      </div>
      <div :class="['w-12 h-12 rounded-lg flex items-center justify-center', bgColor]">
        <component :is="icon" :size="24" :class="iconColor" />
      </div>
    </div>

    <div v-if="loading" class="animate-pulse">
      <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
    </div>
    <div v-else>
      <p :class="['text-3xl font-bold', textColor]">{{ formattedValue }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { TrendingUp, TrendingDown } from 'lucide-vue-next';

const props = defineProps({
  label: String,
  value: [Number, String],
  change: String,
  icon: Object,
  color: String,
  loading: Boolean
});

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString();
  }
  return props.value;
});

const colorMap = {
  blue: {
    bg: 'bg-blue-100 dark:bg-blue-900/30',
    icon: 'text-blue-600 dark:text-blue-400',
    text: 'text-blue-600 dark:text-blue-400'
  },
  green: {
    bg: 'bg-green-100 dark:bg-green-900/30',
    icon: 'text-green-600 dark:text-green-400',
    text: 'text-green-600 dark:text-green-400'
  },
  red: {
    bg: 'bg-red-100 dark:bg-red-900/30',
    icon: 'text-red-600 dark:text-red-400',
    text: 'text-red-600 dark:text-red-400'
  },
  yellow: {
    bg: 'bg-yellow-100 dark:bg-yellow-900/30',
    icon: 'text-yellow-600 dark:text-yellow-400',
    text: 'text-yellow-600 dark:text-yellow-400'
  },
  purple: {
    bg: 'bg-purple-100 dark:bg-purple-900/30',
    icon: 'text-purple-600 dark:text-purple-400',
    text: 'text-purple-600 dark:text-purple-400'
  }
};

const bgColor = computed(() => colorMap[props.color]?.bg || 'bg-gray-100 dark:bg-gray-700');
const iconColor = computed(() => colorMap[props.color]?.icon || 'text-gray-600 dark:text-gray-400');
const textColor = computed(() => colorMap[props.color]?.text || 'text-gray-900 dark:text-gray-100');

const changeColor = computed(() => {
  if (!props.change) return '';
  if (props.change.startsWith('+')) return 'text-green-600 dark:text-green-400';
  if (props.change.startsWith('-')) return 'text-red-600 dark:text-red-400';
  return 'text-gray-600 dark:text-gray-400';
});

const changeIcon = computed(() => {
  if (!props.change) return null;
  return props.change.startsWith('+') ? TrendingUp : TrendingDown;
});
</script>
