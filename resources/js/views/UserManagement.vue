<template>
  <div class="bg-gray-100 min-h-screen">
      <Navbar />
      <div class="flex">
          <aside class="w-64 bg-gray-900 text-white min-h-screen px-4 py-6">
              <!-- Sidebar content matching AdminDashboard -->
          </aside>
          <main class="flex-1 p-8">
              <h1 class="text-3xl font-bold mb-6">User Management</h1>
              <div class="bg-white rounded shadow-sm overflow-hidden">
                  <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                          <tr>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                          </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200">
                          <tr v-for="user in users" :key="user.id">
                              <td class="px-6 py-4 flex items-center">
                                  <img :src="user.avatar" class="w-8 h-8 rounded-full mr-3">
                                  <span>{{ user.name }}</span>
                              </td>
                              <td class="px-6 py-4 text-sm">{{ user.email }}</td>
                              <td class="px-6 py-4 text-sm">
                                  <span v-for="role in user.roles" :key="role.id" class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded mr-1">
                                      {{ role.name }}
                                  </span>
                              </td>
                              <td class="px-6 py-4 text-right">
                                  <button class="text-red-600 hover:text-red-900 text-xs font-bold uppercase">Ban</button>
                              </td>
                          </tr>
                      </tbody>
                  </table>
              </div>
          </main>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';

const users = ref([]);

onMounted(async () => {
    const res = await axios.get('/api/admin/users');
    users.value = res.data.data;
});
</script>
