<template>
  <div 
    class="animate-pulse bg-slate-200 dark:bg-slate-700 rounded"
    :class="[
      className,
      { 'rounded-full': type === 'circle' },
      { 'rounded-md': type === 'text' }
    ]"
    :style="{ 
      width: width, 
      height: height 
    }"
  ></div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'text', // text, circle, rect
    validator: (value) => ['text', 'circle', 'rect'].includes(value)
  },
  width: {
    type: String,
    default: '100%'
  },
  height: {
    type: String,
    default: '1rem'
  },
  className: {
    type: String,
    default: ''
  }
});
</script>
