<template>
  <div class="bg-slate-50 min-h-screen flex font-inter">
      <AdminSidebar @logout="handleLogout" />
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
            <h1 class="text-xl font-bold text-slate-800">User Management</h1>
            <div class="flex items-center gap-4">
                 <div class="h-10 w-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold">
                     {{ auth.user?.name?.[0] || 'A' }}
                 </div>
            </div>
        </header>

        <div class="flex-1 overflow-auto p-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Total Users</div>
                <div class="text-2xl font-bold text-slate-800">{{ pagination.total || 0 }}</div>
              </div>
              <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Active</div>
                <div class="text-2xl font-bold text-green-600">{{ statusCounts.active || 0 }}</div>
              </div>
              <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Banned</div>
                <div class="text-2xl font-bold text-red-600">{{ statusCounts.banned || 0 }}</div>
              </div>
              <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Deactivated</div>
                <div class="text-2xl font-bold text-slate-500">{{ statusCounts.deactivated || 0 }}</div>
              </div>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Toolbar with Filters -->
                <div class="p-4 border-b border-slate-100 flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-md">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input 
                            v-model="searchQuery" 
                            @input="handleSearch"
                            type="text" 
                            placeholder="Search by name or email..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                    </div>
                    <select v-model="statusFilter" @change="fetchUsers(1)" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">
                      <option value="">All Status</option>
                      <option value="active">Active</option>
                      <option value="banned">Banned</option>
                      <option value="deactivated">Deactivated</option>
                    </select>
                    <select v-model="roleFilter" @change="fetchUsers(1)" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">
                      <option value="">All Roles</option>
                      <option value="admin">Admin</option>
                      <option value="instructor">Instructor</option>
                      <option value="user">User</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold">User</th>
                                <th class="px-6 py-4 font-semibold">Role</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                                <th class="px-6 py-4 font-semibold">Joined</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users" :key="user.id" :class="{'bg-red-50/50': user.status === 'banned'}" class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                                            {{ user.name?.[0]?.toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-800">{{ user.name }}</div>
                                            <div class="text-xs text-slate-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                      <span v-for="role in user.roles" :key="role.id" 
                                          :class="[
                                              'text-xs px-2 py-1 rounded-full font-medium',
                                              role.name === 'admin' ? 'bg-purple-100 text-purple-700' : 
                                              role.name === 'instructor' ? 'bg-blue-100 text-blue-700' : 
                                              'bg-slate-100 text-slate-600'
                                          ]"
                                      >
                                          {{ role.name }}
                                      </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                      'px-2 py-1 text-xs font-medium rounded-full',
                                      user.status === 'active' ? 'bg-green-100 text-green-700' :
                                      user.status === 'banned' ? 'bg-red-100 text-red-700' :
                                      'bg-slate-100 text-slate-600'
                                    ]">
                                      {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Profile -->
                                        <button @click="openEditModal(user)" class="p-2 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg" title="Edit Profile">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <!-- Role Management -->
                                        <button @click="openRoleModal(user)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Manage Role">
                                            <UserCog class="w-4 h-4" />
                                        </button>
                                        <!-- Status Toggle -->
                                        <button 
                                            v-if="user.id !== auth.user.id"
                                            @click="openStatusModal(user)" 
                                            class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg"
                                            title="Change Status"
                                        >
                                            <ShieldAlert class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-slate-100 flex items-center justify-between" v-if="pagination.total > 0">
                    <span class="text-sm text-slate-500">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} users
                    </span>
                    <div class="flex gap-2">
                        <button 
                            @click="loadPage(pagination.current_page - 1)" 
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50"
                        >
                            Previous
                        </button>
                         <button 
                            @click="loadPage(pagination.current_page + 1)" 
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
      </main>

      <!-- Edit Profile Modal -->
      <div v-if="editModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="editModalVisible = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
          <div class="p-6 border-b"><h3 class="text-lg font-bold">Edit User Profile</h3></div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
              <input v-model="editForm.name" class="w-full px-4 py-2 border rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
              <input v-model="editForm.email" type="email" class="w-full px-4 py-2 border rounded-lg" />
            </div>
          </div>
          <div class="p-4 bg-slate-50 flex justify-end gap-2">
            <button @click="editModalVisible = false" class="px-4 py-2 bg-slate-200 rounded-lg">Cancel</button>
            <button @click="saveProfile" class="px-4 py-2 bg-purple-600 text-white rounded-lg">Save</button>
          </div>
        </div>
      </div>

      <!-- Role Management Modal -->
      <div v-if="roleModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="roleModalVisible = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
          <div class="p-6 border-b"><h3 class="text-lg font-bold">Manage Role: {{ selectedUser?.name }}</h3></div>
          <div class="p-6 space-y-3">
            <button @click="updateRole('promote_to_instructor')" class="w-full p-4 text-left bg-blue-50 rounded-lg hover:bg-blue-100 border border-blue-200">
              <div class="font-medium text-blue-700">Promote to Instructor</div>
              <div class="text-sm text-blue-500">Grant instructor privileges</div>
            </button>
            <button @click="updateRole('grant_admin')" class="w-full p-4 text-left bg-purple-50 rounded-lg hover:bg-purple-100 border border-purple-200">
              <div class="font-medium text-purple-700">Grant Admin</div>
              <div class="text-sm text-purple-500">Add admin role</div>
            </button>
            <button @click="updateRole('revoke_instructor')" class="w-full p-4 text-left bg-slate-50 rounded-lg hover:bg-slate-100 border border-slate-200">
              <div class="font-medium text-slate-700">Revoke Instructor</div>
              <div class="text-sm text-slate-500">Remove instructor role</div>
            </button>
            <button @click="updateRole('revoke_admin')" class="w-full p-4 text-left bg-red-50 rounded-lg hover:bg-red-100 border border-red-200">
              <div class="font-medium text-red-700">Revoke Admin</div>
              <div class="text-sm text-red-500">Remove admin role</div>
            </button>
          </div>
          <div class="p-4 bg-slate-50 flex justify-end">
            <button @click="roleModalVisible = false" class="px-4 py-2 bg-slate-200 rounded-lg">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Status Change Modal -->
      <div v-if="statusModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="statusModalVisible = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
          <div class="p-6 border-b"><h3 class="text-lg font-bold">Change Status: {{ selectedUser?.name }}</h3></div>
          <div class="p-6 space-y-3">
            <button @click="updateStatus('active')" class="w-full p-4 text-left bg-green-50 rounded-lg hover:bg-green-100 border border-green-200">
              <div class="font-medium text-green-700">Active</div>
              <div class="text-sm text-green-500">User can access all features</div>
            </button>
            <button @click="updateStatus('deactivated')" class="w-full p-4 text-left bg-slate-50 rounded-lg hover:bg-slate-100 border border-slate-200">
              <div class="font-medium text-slate-700">Deactivated</div>
              <div class="text-sm text-slate-500">User cannot login</div>
            </button>
            <button @click="updateStatus('banned')" class="w-full p-4 text-left bg-red-50 rounded-lg hover:bg-red-100 border border-red-200">
              <div class="font-medium text-red-700">Banned</div>
              <div class="text-sm text-red-500">Permanently block user access</div>
            </button>
          </div>
          <div class="p-4 bg-slate-50 flex justify-end">
            <button @click="statusModalVisible = false" class="px-4 py-2 bg-slate-200 rounded-lg">Cancel</button>
          </div>
        </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import { Search, ShieldAlert, Pencil, UserCog } from 'lucide-vue-next';
import { debounce } from 'lodash';
import { confirmAction, confirmUpdate, showSuccess, showError } from '../utils/sweetalert';

const auth = useAuthStore();
const router = useRouter();

const users = ref([]);
const pagination = ref({});
const searchQuery = ref('');
const statusFilter = ref('');
const roleFilter = ref('');

const editModalVisible = ref(false);
const roleModalVisible = ref(false);
const statusModalVisible = ref(false);
const selectedUser = ref(null);
const editForm = ref({ name: '', email: '' });

const statusCounts = computed(() => {
  const counts = { active: 0, banned: 0, deactivated: 0 };
  users.value.forEach(u => { if (counts[u.status] !== undefined) counts[u.status]++; });
  return counts;
});

const fetchUsers = async (page = 1) => {
    try {
        const params = new URLSearchParams({ page });
        if (searchQuery.value) params.append('q', searchQuery.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (roleFilter.value) params.append('role', roleFilter.value);
        
        const res = await axios.get(`/api/admin/users?${params}`);
        users.value = res.data.data;
        pagination.value = {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            from: res.data.from,
            to: res.data.to,
            total: res.data.total
        };
    } catch (e) {
        showError('Failed to load users');
    }
};

const handleSearch = debounce(() => fetchUsers(1), 300);
const loadPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    fetchUsers(page);
};

const openEditModal = (user) => {
  selectedUser.value = user;
  editForm.value = { name: user.name, email: user.email };
  editModalVisible.value = true;
};

const saveProfile = async () => {
  const result = await confirmUpdate('user profile');
  if (!result.isConfirmed) return;
  try {
    await axios.put(`/api/admin/users/${selectedUser.value.id}`, editForm.value);
    showSuccess('Profile updated');
    editModalVisible.value = false;
    fetchUsers();
  } catch (e) {
    showError(e.response?.data?.message || 'Failed to update');
  }
};

const openRoleModal = (user) => {
  selectedUser.value = user;
  roleModalVisible.value = true;
};

const updateRole = async (action) => {
  const result = await confirmAction({ title: 'Confirm Role Change', text: `Apply ${action.replace(/_/g, ' ')} to ${selectedUser.value.name}?`, confirmButtonText: 'Yes, proceed!' });
  if (!result.isConfirmed) return;
  try {
    await axios.post(`/api/admin/users/${selectedUser.value.id}/role`, { action });
    showSuccess('Role updated');
    roleModalVisible.value = false;
    fetchUsers();
  } catch (e) {
    showError(e.response?.data?.message || 'Failed to update role');
  }
};

const openStatusModal = (user) => {
  selectedUser.value = user;
  statusModalVisible.value = true;
};

const updateStatus = async (status) => {
  const result = await confirmAction({ title: 'Confirm Status Change', text: `Set ${selectedUser.value.name}'s status to ${status}?`, confirmButtonText: 'Yes, proceed!' });
  if (!result.isConfirmed) return;
  try {
    await axios.post(`/api/admin/users/${selectedUser.value.id}/status`, { status });
    showSuccess('Status updated');
    statusModalVisible.value = false;
    fetchUsers();
  } catch (e) {
    showError(e.response?.data?.message || 'Failed to update status');
  }
};

const handleLogout = async () => {
    await auth.logout();
    router.push('/login');
};

onMounted(() => fetchUsers());
</script>
