<template>
  <aside class="w-64 bg-slate-900 border-r border-slate-800 text-slate-300 min-h-screen flex flex-col transition-all duration-300 z-50">
    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950/50">
        <router-link to="/" class="flex items-center gap-2 group">
             <div class="bg-purple-600 p-1.5 rounded-lg group-hover:bg-purple-500 transition-colors">
                 <LayoutDashboard class="w-5 h-5 text-white" />
             </div>
             <span class="font-bold text-white text-lg tracking-tight">NetLearn</span>
        </router-link>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto custom-scrollbar">
        <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Overview</div>
        
        <router-link to="/admin" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'hover:bg-slate-800 hover:text-white'
               ]">
                <LayoutTemplate class="w-5 h-5" />
                Dashboard
            </a>
        </router-link>

        <router-link to="/admin/users" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'text-slate-400 hover:bg-slate-800 hover:text-white'
               ]">
                 <Users class="w-5 h-5" :class="isActive ? 'text-white' : 'text-slate-500 group-hover:text-purple-400'" />
                 Users
            </a>
        </router-link>

        <router-link to="/admin/promo-codes" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'text-slate-400 hover:bg-slate-800 hover:text-white'
               ]">
                 <Tags class="w-5 h-5" :class="isActive ? 'text-white' : 'text-slate-500 group-hover:text-purple-400'" />
                 Promo Codes
            </a>
        </router-link>

        <div class="mt-8 px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">System</div>

        <router-link to="/admin/settings" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'text-slate-400 hover:bg-slate-800 hover:text-white'
               ]">
                 <Settings class="w-5 h-5" :class="isActive ? 'text-white' : 'text-slate-500 group-hover:text-purple-400'" />
                 Settings
            </a>
        </router-link>
    </nav>

    <!-- User & Logout -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/30">
        <button @click="$emit('logout')" class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all group">
            <LogOut class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
            Sign Out
        </button>
    </div>
  </aside>
</template>

<script setup>
import { LayoutDashboard, LayoutTemplate, Users, BookOpen, Settings, LogOut, Tags } from 'lucide-vue-next';

defineEmits(['logout']);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}
</style>
