<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200" @click.self="$emit('close')">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-slate-200 dark:border-slate-700">
      <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
           <Keyboard class="w-5 h-5 text-purple-600 dark:text-purple-400" /> 
           Keyboard Shortcuts
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
          <X class="w-5 h-5" />
        </button>
      </div>
      
      <div class="p-2 overflow-y-auto max-h-[60vh]">
        <div class="grid gap-1">
          <div v-for="(shortcut, index) in shortcuts" :key="index" class="flex items-center justify-between p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-lg transition-colors">
             <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ shortcut.description }}</span>
             <div class="flex gap-1">
                <kbd v-for="key in shortcut.keys" :key="key" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-md text-xs font-mono font-bold text-slate-700 dark:text-slate-200 shadow-sm min-w-[24px] text-center">
                    {{ key.toUpperCase() }}
                </kbd>
             </div>
          </div>
        </div>
      </div>
      
      <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 text-center">
         <p class="text-xs text-slate-500 dark:text-slate-400">Press <kbd class="font-bold">?</kbd> anywhere to toggle this menu</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Keyboard, X } from 'lucide-vue-next';

defineProps({
  show: Boolean,
  shortcuts: Array
});

defineEmits(['close']);
</script>
