<template>
  <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 relative overflow-hidden group hover:shadow-md transition-shadow duration-300">
    <div class="flex justify-between items-start z-10 relative">
        <div>
            <div class="text-slate-500 text-sm font-medium mb-1">{{ title }}</div>
            <div class="text-2xl font-bold text-slate-800 tracking-tight">{{ value }}</div>
        </div>
        <div :class="['p-3 rounded-lg bg-opacity-10 transition-transform group-hover:scale-110', colorClass]">
            <slot name="icon"></slot>
        </div>
    </div>
    
    <!-- Decorative background circle -->
    <div :class="['absolute -bottom-4 -right-4 w-24 h-24 rounded-full opacity-5 group-hover:scale-125 transition-transform duration-500', bgClass]"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: [String, Number],
    type: {
        type: String,
        default: 'primary' // primary, success, warning, info
    }
});

const colorClass = computed(() => {
    switch(props.type) {
        case 'success': return 'bg-emerald-500 text-emerald-600';
        case 'warning': return 'bg-amber-500 text-amber-600';
        case 'purple': return 'bg-purple-500 text-purple-600';
        case 'blue': return 'bg-blue-500 text-blue-600';
        default: return 'bg-slate-500 text-slate-600';
    }
});

const bgClass = computed(() => {
    switch(props.type) {
        case 'success': return 'bg-emerald-500';
        case 'warning': return 'bg-amber-500';
        case 'purple': return 'bg-purple-500';
        case 'blue': return 'bg-blue-500';
        default: return 'bg-slate-500';
    }
});
</script>
