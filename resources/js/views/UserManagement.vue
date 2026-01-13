<template>
  <div class="bg-gray-50 min-h-screen flex font-inter">
      <AdminSidebar @logout="handleLogout" />
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
            <h1 class="text-xl font-bold text-slate-800">User Management</h1>
            <div class="flex items-center gap-4">
                 <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300">
                     {{ auth.user?.name?.[0] || 'A' }}
                 </div>
            </div>
        </header>

        <div class="flex-1 overflow-auto p-8">
            <!-- Tabs -->
            <div class="flex space-x-1 rounded-xl bg-slate-200 p-1 mb-6 max-w-md">
                <button 
                  v-for="tab in ['All Users', 'Instructor Requests']" 
                  :key="tab"
                  @click="activeTab = tab"
                  :class="[
                    'w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all',
                    activeTab === tab
                      ? 'bg-white text-purple-700 shadow'
                      : 'text-slate-600 hover:bg-white/[0.12] hover:text-slate-800'
                  ]"
                >
                  {{ tab }}
                </button>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Toolbar (Only for All Users) -->
                <div v-if="activeTab === 'All Users'" class="p-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="relative w-full sm:w-96">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input 
                            v-model="searchQuery" 
                            @input="handleSearch"
                            type="text" 
                            placeholder="Search users by name or email..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold">User</th>
                                <th class="px-6 py-4 font-semibold">Role</th>
                                <th class="px-6 py-4 font-semibold">Joined</th>
                                <th class="px-6 py-4 font-semibold text-right">Status & Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in visibleUsers" :key="user.id" :class="{'bg-red-50/50': user.is_banned, 'hover:bg-slate-50': !user.is_banned}" class="transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-sm font-bold text-slate-600">
                                            {{ user.name[0] }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900">{{ user.name }}</div>
                                            <div class="text-xs text-slate-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <span v-for="role in user.roles" :key="role.id" 
                                        :class="[
                                            'text-xs px-2.5 py-1 rounded-full font-medium border',
                                            role.name === 'admin' ? 'bg-purple-100 text-purple-700 border-purple-200' : 
                                            role.name === 'instructor' ? 'bg-blue-100 text-blue-700 border-blue-200' : 
                                            'bg-slate-100 text-slate-600 border-slate-200'
                                        ]"
                                     >
                                        {{ role.name }}
                                     </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <!-- Actions for All Users -->
                                    <div v-if="activeTab === 'All Users'" class="flex items-center justify-end gap-3">
                                        <span v-if="user.is_banned" class="text-xs font-medium text-red-600 bg-red-100 px-2 py-0.5 rounded">Banned</span>
                                        <button 
                                            v-if="user.id !== auth.user.id"
                                            @click="toggleBan(user)" 
                                            :title="user.is_banned ? 'Unban User' : 'Ban User'"
                                            :class="[
                                                'p-2 rounded-lg transition-colors',
                                                user.is_banned 
                                                    ? 'text-emerald-600 hover:bg-emerald-50' 
                                                    : 'text-slate-400 hover:text-red-600 hover:bg-red-50'
                                            ]"
                                        >
                                            <ShieldAlert class="w-4 h-4" v-if="!user.is_banned" />
                                            <CheckCircle class="w-4 h-4" v-else />
                                        </button>
                                    </div>

                                    <!-- Actions for Instructor Requests -->
                                    <div v-else class="flex items-center justify-end gap-2">
                                        <button @click="verifyInstructor(user, 'approve')" class="flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded text-xs font-bold transition-colors">
                                            <CheckCircle class="w-3.5 h-3.5" /> Approve
                                        </button>
                                        <button @click="verifyInstructor(user, 'reject')" class="flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-bold transition-colors">
                                            <XCircle class="w-3.5 h-3.5" /> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="visibleUsers.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                    {{ activeTab === 'All Users' ? 'No users found matching your search.' : 'No pending instructor requests.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (Only for All Users) -->
                <div class="p-4 border-t border-slate-100 flex items-center justify-between" v-if="activeTab === 'All Users' && pagination.total > 0">
                    <span class="text-sm text-slate-500">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} users
                    </span>
                    <div class="flex gap-2">
                        <button 
                            @click="loadPage(pagination.current_page - 1)" 
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                         <button 
                            @click="loadPage(pagination.current_page + 1)" 
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
      </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import { Search, ShieldAlert, CheckCircle, XCircle } from 'lucide-vue-next';
import { debounce } from 'lodash';

const auth = useAuthStore();
const router = useRouter();

const activeTab = ref('All Users');
const users = ref([]);
const pendingInstructors = ref([]);
const pagination = ref({});
const searchQuery = ref('');

const visibleUsers = computed(() => {
    return activeTab.value === 'All Users' ? users.value : pendingInstructors.value;
});

const fetchUsers = async (page = 1) => {
    try {
        const res = await axios.get('/api/admin/users', {
            params: {
                page,
                q: searchQuery.value
            }
        });
        users.value = res.data.data;
        pagination.value = {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            from: res.data.from,
            to: res.data.to,
            total: res.data.total
        };
    } catch (e) {
        console.error(e);
    }
};

const fetchPendingInstructors = async () => {
    try {
        const res = await axios.get('/api/admin/instructors/pending');
        pendingInstructors.value = res.data;
    } catch (e) {
        console.error(e);
    }
};

// Refetch when tab changes
watch(activeTab, (newTab) => {
    if (newTab === 'All Users') fetchUsers();
    else fetchPendingInstructors();
});

const handleSearch = debounce(() => {
    fetchUsers(1);
}, 300);

const loadPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    fetchUsers(page);
};

const toggleBan = async (user) => {
    const action = user.is_banned ? 'Unban' : 'Ban';
    if (!confirm(`Are you sure you want to ${action} ${user.name}?`)) return;

    try {
        const res = await axios.post(`/api/admin/users/${user.id}/ban`);
        user.is_banned = res.data.is_banned;
    } catch (e) {
        alert(e.response?.data?.message || 'Action failed');
    }
};

const verifyInstructor = async (user, action) => {
    if (!confirm(`Are you sure you want to ${action} this instructor application?`)) return;
    try {
        await axios.post(`/api/admin/instructors/${user.id}/verify`, { action });
        // Remove from list
        pendingInstructors.value = pendingInstructors.value.filter(u => u.id !== user.id);
        // Maybe show toast?
    } catch (e) {
         alert('Action failed');
    }
};

const handleLogout = () => {
    auth.logout();
    router.push('/login');
};

onMounted(() => {
    fetchUsers();
});
</script>
