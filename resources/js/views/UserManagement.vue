<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
      <AdminSidebar @logout="handleLogout" />
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
      <AdminHeader :title="$t('admin.users.title')" @logout="handleLogout" />

        <div class="flex-1 overflow-auto p-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.users.total_users') }}</div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ pagination.total || 0 }}</div>
              </div>
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.users.active') }}</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ statusCounts.active || 0 }}</div>
              </div>
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.users.banned') }}</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ statusCounts.banned || 0 }}</div>
              </div>
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.users.deactivated') }}</div>
                <div class="text-2xl font-bold text-slate-500 dark:text-slate-400">{{ statusCounts.deactivated || 0 }}</div>
              </div>
            </div>

            <!-- Content -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
                <!-- Toolbar with Filters -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex flex-wrap items-center gap-4 transition-colors">
                    <div class="relative flex-1 min-w-[200px] max-w-md">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input 
                            v-model="searchQuery" 
                            @input="handleSearch"
                            type="text" 
                            :placeholder="$t('admin.users.search_placeholder')" 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                        >
                    </div>
                    <select v-model="statusFilter" @change="fetchUsers(1)" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-white transition-colors">
                      <option value="">{{ $t('admin.users.all_status') }}</option>
                      <option value="active">{{ $t('admin.users.active') }}</option>
                      <option value="banned">{{ $t('admin.users.banned') }}</option>
                      <option value="deactivated">{{ $t('admin.users.deactivated') }}</option>
                    </select>
                    <select v-model="roleFilter" @change="fetchUsers(1)" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-white transition-colors">
                      <option value="">{{ $t('admin.users.all_roles') }}</option>
                      <option value="admin">{{ $t('admin.users.admin') }}</option>
                      <option value="instructor">{{ $t('admin.users.instructor') }}</option>
                      <option value="user">{{ $t('admin.users.user') }}</option>
                    </select>
                </div>

                <!-- Bulk Actions Toolbar -->
                <div v-if="selectedUsers.length > 0" class="bg-purple-50 dark:bg-purple-900/20 p-4 border-b border-purple-100 dark:border-purple-800/50 flex items-center justify-between animate-in fade-in slide-in-from-top-2 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-bold text-purple-700 dark:text-purple-300">{{ $t('admin.users.selected_users', {count: selectedUsers.length}) }}</span>
                        <div class="h-4 w-px bg-purple-200 dark:bg-purple-800"></div>
                        <button @click="selectedUsers = []; selectAll = false" class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 hover:underline">
                            {{ $t('admin.users.deselect_all') }}
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="handleBulkAction('activate')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-purple-200 dark:border-slate-600 text-green-600 dark:text-green-400 rounded-lg text-sm font-medium hover:bg-green-50 dark:hover:bg-green-900/20 shadow-sm transition-colors">
                            {{ $t('admin.users.activate') }}
                        </button>
                        <button @click="handleBulkAction('deactivate')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-purple-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-colors">
                            {{ $t('admin.users.deactivate') }}
                        </button>
                        <button @click="handleBulkAction('ban')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-purple-200 dark:border-slate-600 text-orange-600 dark:text-orange-400 rounded-lg text-sm font-medium hover:bg-orange-50 dark:hover:bg-orange-900/20 shadow-sm transition-colors">
                            {{ $t('admin.users.ban') }}
                        </button>
                        <div class="h-4 w-px bg-purple-200 dark:bg-purple-800 mx-2"></div>
                        <button @click="handleBulkAction('delete')" class="px-3 py-1.5 bg-red-600 dark:bg-red-700 text-white rounded-lg text-sm font-medium hover:bg-red-700 dark:hover:bg-red-600 shadow-sm transition-colors flex items-center gap-2">
                            <Trash2 class="w-4 h-4" /> {{ $t('admin.users.delete') }}
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 transition-colors">
                            <tr>
                                <th class="px-6 py-4 w-12">
                                    <input type="checkbox" v-model="selectAll" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-purple-600 focus:ring-purple-500 w-4 h-4 transition-colors" />
                                </th>
                                <th class="px-6 py-4 font-semibold">{{ $t('admin.users.th_user') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ $t('admin.users.th_role') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ $t('admin.users.th_status') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ $t('admin.users.th_joined') }}</th>
                                <th class="px-6 py-4 font-semibold text-right">{{ $t('admin.users.th_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                            <template v-if="loading">
                                <tr v-for="n in 5" :key="n" class="animate-pulse border-b border-slate-100 dark:border-slate-700">
                                    <td class="px-6 py-4"><SkeletonLoader width="1rem" height="1rem" /></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <SkeletonLoader type="circle" width="2.5rem" height="2.5rem" />
                                            <div class="space-y-2">
                                                <SkeletonLoader width="8rem" height="1rem" />
                                                <SkeletonLoader width="10rem" height="0.75rem" />
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4"><SkeletonLoader width="6rem" height="1.5rem" className="rounded-full" /></td>
                                    <td class="px-6 py-4"><SkeletonLoader width="4rem" height="1.5rem" className="rounded-full" /></td>
                                    <td class="px-6 py-4"><SkeletonLoader width="6rem" height="1rem" /></td>
                                    <td class="px-6 py-4"><SkeletonLoader width="8rem" height="2rem" className="ml-auto" /></td>
                                </tr>
                            </template>
                            <template v-else>
                            <tr v-for="user in users" :key="user.id" :class="{'bg-purple-50/30 dark:bg-purple-900/10': selectedUsers.includes(user.id), 'bg-red-50/50 dark:bg-red-900/10': user.status === 'banned' && !selectedUsers.includes(user.id)}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <input 
                                        v-if="user.id !== auth.user.id"
                                        type="checkbox" 
                                        :value="user.id" 
                                        v-model="selectedUsers" 
                                        class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-purple-600 focus:ring-purple-500 w-4 h-4 transition-colors" 
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                            {{ user.name?.[0]?.toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-800 dark:text-white transition-colors">{{ user.name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 transition-colors">{{ user.email }}</div>
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
                                      {{ $t(`admin.users.${user.status}`) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">
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
                                        <!-- Email User -->
                                        <button @click="openEmailModal(user)" class="p-2 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Email User">
                                            <Mail class="w-4 h-4" />
                                        </button>
                                        <!-- View Activity -->
                                        <button @click="openActivityModal(user)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg" title="View Activity">
                                            <Activity class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                    {{ $t('admin.users.no_users') }}
                                </td>
                            </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between transition-colors" v-if="pagination.total > 0">
                    <span class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $t('admin.users.showing', {from: pagination.from, to: pagination.to, total: pagination.total}) }}
                    </span>
                    <div class="flex gap-2">
                        <button 
                            @click="loadPage(pagination.current_page - 1)" 
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-1 text-sm border border-slate-200 dark:border-slate-700 dark:text-slate-300 rounded hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 transition-colors"
                        >
                            {{ $t('admin.users.previous') }}
                        </button>
                         <button 
                            @click="loadPage(pagination.current_page + 1)" 
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-1 text-sm border border-slate-200 dark:border-slate-700 dark:text-slate-300 rounded hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 transition-colors"
                        >
                            {{ $t('admin.users.next') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
      </main>

      <!-- Edit Profile Modal -->
      <div v-if="editModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="editModalVisible = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 transition-colors">
          <div class="p-6 border-b border-slate-200 dark:border-slate-700"><h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $t('admin.users.modal_edit_title') }}</h3></div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
              <input v-model="editForm.name" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
              <input v-model="editForm.email" type="email" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors" />
            </div>
          </div>
          <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-2 rounded-b-2xl">
            <button @click="editModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">{{ $t('admin.users.cancel') }}</button>
            <button @click="saveProfile" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">{{ $t('admin.users.save') }}</button>
          </div>
        </div>
      </div>

      <!-- Role Management Modal -->
      <div v-if="roleModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="roleModalVisible = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 transition-colors">
          <div class="p-6 border-b border-slate-200 dark:border-slate-700"><h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $t('admin.users.modal_role_title', {name: selectedUser?.name}) }}</h3></div>
          <div class="p-6 space-y-3">
            <button @click="updateRole('promote_to_instructor')" class="w-full p-4 text-left bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 border border-blue-200 dark:border-blue-800 transition-colors">
              <div class="font-medium text-blue-700 dark:text-blue-400">{{ $t('admin.users.promote_instructor') }}</div>
              <div class="text-sm text-blue-500 dark:text-blue-300">{{ $t('admin.users.grant_instructor_privileges') }}</div>
            </button>
            <button @click="updateRole('grant_admin')" class="w-full p-4 text-left bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 border border-purple-200 dark:border-purple-800 transition-colors">
              <div class="font-medium text-purple-700 dark:text-purple-400">{{ $t('admin.users.grant_admin') }}</div>
              <div class="text-sm text-purple-500 dark:text-purple-300">{{ $t('admin.users.add_admin_role') }}</div>
            </button>
            <button @click="updateRole('revoke_instructor')" class="w-full p-4 text-left bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition-colors">
              <div class="font-medium text-slate-700 dark:text-slate-300">{{ $t('admin.users.revoke_instructor') }}</div>
              <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.users.remove_instructor_role') }}</div>
            </button>
            <button @click="updateRole('revoke_admin')" class="w-full p-4 text-left bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 border border-red-200 dark:border-red-800 transition-colors">
              <div class="font-medium text-red-700 dark:text-red-400">{{ $t('admin.users.revoke_admin') }}</div>
              <div class="text-sm text-red-500 dark:text-red-300">{{ $t('admin.users.remove_admin_role') }}</div>
            </button>
          </div>
          <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end rounded-b-2xl">
            <button @click="roleModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">{{ $t('admin.users.cancel') }}</button>
          </div>
        </div>
      </div>

      <!-- Status Change Modal -->
      <div v-if="statusModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="statusModalVisible = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 transition-colors">
          <div class="p-6 border-b border-slate-200 dark:border-slate-700"><h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $t('admin.users.modal_status_title', {name: selectedUser?.name}) }}</h3></div>
          <div class="p-6 space-y-3">
            <button @click="updateStatus('active')" class="w-full p-4 text-left bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 border border-green-200 dark:border-green-800 transition-colors">
              <div class="font-medium text-green-700 dark:text-green-400">{{ $t('admin.users.active') }}</div>
              <div class="text-sm text-green-500 dark:text-green-300">{{ $t('admin.users.user_can_access') }}</div>
            </button>
            <button @click="updateStatus('deactivated')" class="w-full p-4 text-left bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition-colors">
              <div class="font-medium text-slate-700 dark:text-slate-300">{{ $t('admin.users.deactivated') }}</div>
              <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.users.user_cannot_login') }}</div>
            </button>
            <button @click="updateStatus('banned')" class="w-full p-4 text-left bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 border border-red-200 dark:border-red-800 transition-colors">
              <div class="font-medium text-red-700 dark:text-red-400">{{ $t('admin.users.banned') }}</div>
              <div class="text-sm text-red-500 dark:text-red-300">{{ $t('admin.users.permanently_block') }}</div>
            </button>
          </div>
          <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end rounded-b-2xl">
            <button @click="statusModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">{{ $t('admin.users.cancel') }}</button>
          </div>
        </div>
      </div>

      <!-- Activity Log Modal -->
      <div v-if="activityModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 animate-in fade-in" @click.self="activityModalVisible = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 h-[80vh] flex flex-col transition-colors">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center transition-colors">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $t('admin.users.modal_activity_title', {name: selectedUser?.name}) }}</h3>
                <button @click="activityModalVisible = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <X class="w-5 h-5" />
                </button>
            </div>
            <div class="flex-1 overflow-auto p-0">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 sticky top-0 transition-colors">
                        <tr>
                            <th class="px-6 py-3">{{ $t('admin.users.activity_action') }}</th>
                            <th class="px-6 py-3">{{ $t('admin.users.activity_ip') }}</th>
                            <th class="px-6 py-3">{{ $t('admin.users.activity_date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                        <tr v-for="log in activityLogs" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300">
                                {{ log.action }}
                                <div v-if="log.action === 'login' && log.user_agent" class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-xs" :title="log.user_agent">
                                    {{ log.user_agent }}
                                </div>
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-500 dark:text-slate-400">{{ log.ip_address }}</td>
                            <td class="px-6 py-3 text-sm text-slate-500 dark:text-slate-400">{{ new Date(log.created_at).toLocaleString() }}</td>
                        </tr>
                        <tr v-if="activityLogs.length === 0">
                            <td colspan="3" class="px-6 py-8 text-center text-slate-400 italic">{{ $t('admin.users.no_activity') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-2xl transition-colors">
                <!-- Simple pagination for logs if needed, or just load latest 20 as implemented -->
                <div class="text-xs text-center text-slate-500 dark:text-slate-400">{{ $t('admin.users.showing_latest') }}</div>
            </div>
        </div>
      </div>

      <!-- Email User Modal -->
      <div v-if="emailModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 animate-in fade-in" @click.self="emailModalVisible = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 transition-colors">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700"><h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $t('admin.users.modal_email_title', {name: selectedUser?.name}) }}</h3></div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ $t('admin.users.subject') }}</label>
                    <input v-model="emailForm.subject" type="text" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors" :placeholder="$t('admin.users.subject')" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ $t('admin.users.message') }}</label>
                    <textarea v-model="emailForm.message" rows="5" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors" :placeholder="$t('admin.users.message')"></textarea>
                </div>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-2 rounded-b-2xl">
                <button @click="emailModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">{{ $t('admin.users.cancel') }}</button>
                <button @click="sendEmail" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 transition-colors">
                    <Send class="w-4 h-4" /> {{ $t('admin.users.send_email') }}
                </button>
            </div>
        </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

import AdminSidebar from '../components/admin/AdminSidebar.vue';
import AdminHeader from '../components/admin/AdminHeader.vue';
import StatCard from '../components/admin/StatCard.vue';
import SkeletonLoader from '../components/common/SkeletonLoader.vue';
import { Users, UserPlus, UserCheck, UserX, Search, Filter, MoreVertical, Shield, Mail, Edit, Trash2, CheckCircle, XCircle, UserCog, ShieldAlert, Activity, X, Send, Pencil } from 'lucide-vue-next';
import { debounce } from 'lodash';
import { confirmAction, confirmUpdate, showSuccess, showError } from '../utils/sweetalert';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const auth = useAuthStore();
const router = useRouter();

const users = ref([]);
const loading = ref(false);
const pagination = ref({});
const searchQuery = ref('');
const statusFilter = ref('');
const roleFilter = ref('');

// Bulk Selection
const selectedUsers = ref([]);
const selectAll = ref(false);

const editModalVisible = ref(false);
const roleModalVisible = ref(false);
const statusModalVisible = ref(false);
const activityModalVisible = ref(false);
const emailModalVisible = ref(false);
const selectedUser = ref(null);
const editForm = ref({ name: '', email: '' });
const emailForm = ref({ subject: '', message: '' });
const activityLogs = ref([]);

const statusCounts = computed(() => {
  const counts = { active: 0, banned: 0, deactivated: 0 };
  users.value.forEach(u => { if (counts[u.status] !== undefined) counts[u.status]++; });
  return counts;
});

// Watch users changes to reset selection
watch(users, () => {
    selectedUsers.value = [];
    selectAll.value = false;
});

// Watch selectAll to toggle all
watch(selectAll, (val) => {
    if (val) {
        // Only select users that are not the current user
        selectedUsers.value = users.value.filter(u => u.id !== auth.user.id).map(u => u.id);
    } else {
        selectedUsers.value = [];
    }
});

const fetchUsers = async (page = 1) => {
    loading.value = true;
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
        showError(t('admin.users.load_error'));
    } finally {
        loading.value = false;
    }
};

const handleSearch = debounce(() => fetchUsers(1), 300);
const loadPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    fetchUsers(page);
};

// Bulk Actions
const handleBulkAction = async (action) => {
    if (selectedUsers.value.length === 0) return;
    
    const count = selectedUsers.value.length;
    const actionText = action === 'delete' ? 'delete' : 
                       action === 'activate' ? 'activate' : 
                       action === 'deactivate' ? 'deactivate' : 'ban';
                       
    const result = await confirmAction({ 
        title: t('admin.users.bulk_action_title', {action: t(`admin.users.${actionText}`)}), 
        text: t('admin.users.bulk_action_confirm', {action: t(`admin.users.${actionText}`), count}), 
        confirmButtonText: t('admin.users.yes_proceed') 
    });
    
    if (!result.isConfirmed) return;
    
    try {
        await axios.post('/api/admin/users/bulk', {
            user_ids: selectedUsers.value,
            action: action
        });
        showSuccess(t('admin.users.bulk_success', {action: t(`admin.users.${actionText}`), count}));
        selectedUsers.value = [];
        selectAll.value = false;
        fetchUsers(pagination.value.current_page);
    } catch (e) {
        showError(t('admin.users.bulk_error'));
    }
};

const openEditModal = (user) => {
  selectedUser.value = user;
  editForm.value = { name: user.name, email: user.email };
  editModalVisible.value = true;
};

const saveProfile = async () => {
  const result = await confirmUpdate(t('admin.users.user_profile'));
  if (!result.isConfirmed) return;
  try {
    await axios.put(`/api/admin/users/${selectedUser.value.id}`, editForm.value);
    showSuccess(t('admin.users.profile_updated'));
    editModalVisible.value = false;
    fetchUsers();
  } catch (e) {
    showError(e.response?.data?.message || t('admin.users.update_error'));
  }
};

const openRoleModal = (user) => {
  selectedUser.value = user;
  roleModalVisible.value = true;
};

const updateRole = async (action) => {
  const result = await confirmAction({ title: t('admin.users.confirm_role_change'), text: t('admin.users.confirm_role_text', {action: action.replace(/_/g, ' '), name: selectedUser.value.name}), confirmButtonText: t('admin.users.yes_proceed') });
  if (!result.isConfirmed) return;
  try {
    await axios.post(`/api/admin/users/${selectedUser.value.id}/role`, { action });
    showSuccess(t('admin.users.role_updated'));
    roleModalVisible.value = false;
    fetchUsers();
  } catch (e) {
    showError(e.response?.data?.message || t('admin.users.update_error'));
  }
};

const openStatusModal = (user) => {
  selectedUser.value = user;
  statusModalVisible.value = true;
};

const updateStatus = async (status) => {
  const result = await confirmAction({ title: t('admin.users.confirm_status_change'), text: t('admin.users.confirm_status_text', {name: selectedUser.value.name, status: t(`admin.users.${status}`)}), confirmButtonText: t('admin.users.yes_proceed') });
  if (!result.isConfirmed) return;
  try {
    await axios.post(`/api/admin/users/${selectedUser.value.id}/status`, { status });
    showSuccess(t('admin.users.status_updated'));
    statusModalVisible.value = false;
    fetchUsers();
  } catch (e) {
    showError(e.response?.data?.message || t('admin.users.update_error'));
  }
};

const openActivityModal = async (user) => {
    selectedUser.value = user;
    activityModalVisible.value = true;
    activityLogs.value = [];
    try {
        const res = await axios.get(`/api/admin/users/${user.id}/activity`);
        activityLogs.value = res.data.data;
    } catch (e) {
        showError(t('admin.users.load_activity_error'));
    }
};

const openEmailModal = (user) => {
    selectedUser.value = user;
    emailForm.value = { subject: '', message: '' };
    emailModalVisible.value = true;
};

const sendEmail = async () => {
    if (!emailForm.value.subject || !emailForm.value.message) {
        showError(t('admin.users.fill_all_fields'));
        return;
    }
    
    try {
        await axios.post(`/api/admin/users/${selectedUser.value.id}/email`, emailForm.value);
        showSuccess(t('admin.users.email_sent'));
        emailModalVisible.value = false;
    } catch (e) {
        showError(e.response?.data?.message || t('admin.users.email_error'));
    }
};

const handleLogout = async () => {
    await auth.logout();
    router.push('/login');
};

onMounted(() => fetchUsers());
</script>
