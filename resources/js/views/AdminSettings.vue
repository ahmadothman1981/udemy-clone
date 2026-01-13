<template>
  <div class="bg-gray-50 min-h-screen flex font-inter">
      <AdminSidebar @logout="handleLogout" />
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
            <h1 class="text-xl font-bold text-slate-800">Settings</h1>
            <div class="flex items-center gap-4">
                 <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300">
                     {{ auth.user?.name?.[0] || 'A' }}
                 </div>
            </div>
        </header>

        <div class="flex-1 overflow-auto p-8">
            <div class="max-w-4xl mx-auto space-y-6">
                <!-- General Settings -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">General Configuration</h2>
                    <form @submit.prevent="saveSettings" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Site Name</label>
                                <input type="text" v-model="settings.siteName" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Support Email</label>
                                <input type="email" v-model="settings.supportEmail" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 pt-2">
                             <input type="checkbox" v-model="settings.maintenanceMode" id="maintenance" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                             <label for="maintenance" class="text-sm text-slate-700">Enable Maintenance Mode</label>
                        </div>
                    </form>
                </div>

                <!-- Stub for other settings -->
                 <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 opacity-75">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex justify-between">
                        Payment Gateways
                        <span class="text-xs bg-slate-100 text-slate-500 px-2 py-1 rounded">Coming Soon</span>
                    </h2>
                    <div class="space-y-4">
                        <div class="p-3 border border-slate-200 rounded-lg bg-slate-50 text-sm text-slate-500 text-center italic">
                            Stripe and PayPal configuration will be available here.
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="saveSettings" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
      </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';

const auth = useAuthStore();
const router = useRouter();

const settings = ref({
    siteName: 'NetLearn',
    supportEmail: 'support@netlearn.com',
    maintenanceMode: false
});

const saveSettings = () => {
    // In a real app, this would make an API call
    alert('Settings saved successfully (Simulation)');
};

const handleLogout = () => {
    auth.logout();
    router.push('/login');
};
</script>
