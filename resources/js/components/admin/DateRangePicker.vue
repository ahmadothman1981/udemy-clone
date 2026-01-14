<template>
  <div class="flex items-center gap-2">
    <!-- Preset Buttons -->
    <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-1 transition-colors">
      <button 
        v-for="preset in presets" 
        :key="preset.days"
        @click="selectPreset(preset.days)"
        :class="[
          'px-3 py-1.5 text-xs font-medium rounded-md transition-all',
          selectedDays === preset.days 
            ? 'bg-white dark:bg-slate-700 text-purple-600 dark:text-purple-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
        ]"
      >
        {{ preset.label }}
      </button>
    </div>

    <!-- Custom Range -->
    <div class="flex items-center gap-2">
      <div class="relative">
        <Calendar class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input 
          type="date" 
          v-model="customStart"
          @change="applyCustomRange"
          class="pl-8 pr-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
          :max="customEnd || today"
        />
      </div>
      <span class="text-slate-400 text-xs">to</span>
      <input 
        type="date" 
        v-model="customEnd"
        @change="applyCustomRange"
        class="px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
        :min="customStart"
        :max="today"
      />
    </div>

    <!-- Compare Toggle -->
    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 cursor-pointer ml-2">
      <input 
        type="checkbox" 
        v-model="compareEnabled"
        @change="$emit('compare-change', compareEnabled)"
        class="w-4 h-4 text-purple-600 rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-purple-500"
      />
      Compare to previous period
    </label>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Calendar } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: Number, default: 30 },
  compare: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'range-change', 'compare-change']);

const presets = [
  { label: '7D', days: 7 },
  { label: '30D', days: 30 },
  { label: '90D', days: 90 },
];

const selectedDays = ref(props.modelValue);
const customStart = ref('');
const customEnd = ref('');
const compareEnabled = ref(props.compare);

const today = computed(() => new Date().toISOString().split('T')[0]);

const selectPreset = (days) => {
  selectedDays.value = days;
  customStart.value = '';
  customEnd.value = '';
  
  const end = new Date();
  const start = new Date();
  start.setDate(start.getDate() - days);
  
  emit('update:modelValue', days);
  emit('range-change', {
    start_date: start.toISOString().split('T')[0],
    end_date: end.toISOString().split('T')[0],
    days
  });
};

const applyCustomRange = () => {
  if (customStart.value && customEnd.value) {
    selectedDays.value = null;
    const start = new Date(customStart.value);
    const end = new Date(customEnd.value);
    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    
    emit('range-change', {
      start_date: customStart.value,
      end_date: customEnd.value,
      days
    });
  }
};

// Initialize with default preset
selectPreset(props.modelValue);
</script>
