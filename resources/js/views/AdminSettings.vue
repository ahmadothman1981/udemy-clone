<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
      <AdminSidebar @logout="handleLogout" />
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 h-16 flex items-center justify-between px-8 sticky top-0 z-30 transition-colors">
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Platform Settings</h1>
            <div class="flex items-center gap-4">
                 <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold transition-colors">
                     {{ auth.user?.name?.[0] || 'A' }}
                 </div>
            </div>
        </header>

        <div class="flex-1 overflow-auto p-8">
            <!-- Tabs -->
            <div class="flex space-x-1 rounded-xl bg-slate-200 dark:bg-slate-800 p-1 mb-6 max-w-2xl transition-colors">
              <button 
                v-for="tab in tabs" 
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-all',
                  activeTab === tab.id 
                    ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' 
                    : 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700 hover:text-slate-800 dark:hover:text-slate-200'
                ]"
              >
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.label }}
              </button>
            </div>

            <!-- Categories Tab -->
            <div v-if="activeTab === 'categories'" class="space-y-6">
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center transition-colors">
                  <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Categories</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Manage course categories</p>
                  </div>
                  <button @click="openCategoryModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center gap-2 transition-colors">
                    <Plus class="w-4 h-4" /> Add Category
                  </button>
                </div>
                <div class="overflow-x-auto">
                  <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase border-b border-slate-200 dark:border-slate-700 transition-colors">
                      <tr>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Slug</th>
                        <th class="px-6 py-4">Courses</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                      <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-800 dark:text-white transition-colors">{{ cat.name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">{{ cat.slug }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">{{ cat.courses_count || 0 }}</td>
                        <td class="px-6 py-4 text-right">
                          <button @click="openCategoryModal(cat)" class="p-2 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors"><Pencil class="w-4 h-4" /></button>
                          <button @click="deleteCategory(cat)" class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors"><Trash2 class="w-4 h-4" /></button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Payment Tab -->
            <div v-if="activeTab === 'payment'" class="space-y-6">
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Payment Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Platform Commission (%)</label>
                    <input v-model="paymentSettings.platform_commission" type="number" min="0" max="100" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white transition-colors" />
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Percentage of each sale retained by the platform</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Minimum Payout ($)</label>
                    <input v-model="paymentSettings.payout_minimum" type="number" min="0" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white transition-colors" />
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Minimum balance required for instructor payouts</p>
                  </div>
                </div>
                <div class="mt-6 flex justify-end">
                  <button @click="savePaymentSettings" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Save Payment Settings</button>
                </div>
              </div>
            </div>

            <!-- Security Tab -->
            <div v-if="activeTab === 'security'" class="space-y-6">
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Security Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Minimum Password Length</label>
                    <input v-model="securitySettings.password_min_length" type="number" min="6" max="32" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white transition-colors" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Session Timeout (minutes)</label>
                    <input v-model="securitySettings.session_timeout" type="number" min="5" max="1440" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white transition-colors" />
                  </div>
                </div>
                <div class="mt-6 flex justify-end">
                  <button @click="saveSecuritySettings" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Save Security Settings</button>
                </div>
              </div>
            </div>

            <!-- Localization Tab -->
            <div v-if="activeTab === 'localization'" class="space-y-6">
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Localization Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Default Language</label>
                    <select v-model="localizationSettings.default_language" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white transition-colors">
                      <option value="en">English</option>
                      <option value="ar">Arabic</option>
                      <option value="es">Spanish</option>
                      <option value="fr">French</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Enabled Languages</label>
                    <div class="flex flex-wrap gap-2">
                      <label v-for="lang in ['en', 'ar', 'es', 'fr']" :key="lang" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                        <input type="checkbox" :value="lang" v-model="localizationSettings.enabled_languages" class="w-4 h-4 text-purple-600 rounded border-slate-300 dark:border-slate-600 focus:ring-purple-500" />
                        {{ lang.toUpperCase() }}
                      </label>
                    </div>
                  </div>
                </div>
                <div class="mt-6 flex justify-end">
                  <button @click="saveLocalizationSettings" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Save Localization Settings</button>
                </div>
              </div>
            </div>
        </div>
      </main>

      <!-- Category Modal -->
      <div v-if="categoryModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="categoryModalVisible = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 transition-colors">
          <div class="p-6 border-b border-slate-200 dark:border-slate-700"><h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ editingCategory ? 'Edit' : 'Add' }} Category</h3></div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
              <input v-model="categoryForm.name" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-purple-500 transition-colors" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug (auto-generated if empty)</label>
              <input v-model="categoryForm.slug" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-purple-500 transition-colors" placeholder="programming-basics" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
              <textarea v-model="categoryForm.description" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-purple-500 transition-colors" rows="3"></textarea>
            </div>
          </div>
          <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-2 rounded-b-2xl">
            <button @click="categoryModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Cancel</button>
            <button @click="saveCategory" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Save</button>
          </div>
        </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import { FolderTree, CreditCard, Shield, Globe, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { confirmDelete, confirmUpdate, showSuccess, showError } from '../utils/sweetalert';

const auth = useAuthStore();
const router = useRouter();

const tabs = [
  { id: 'categories', label: 'Categories', icon: FolderTree },
  { id: 'payment', label: 'Payment', icon: CreditCard },
  { id: 'security', label: 'Security', icon: Shield },
  { id: 'localization', label: 'Localization', icon: Globe },
];

const activeTab = ref('categories');
const categories = ref([]);
const categoryModalVisible = ref(false);
const editingCategory = ref(null);
const categoryForm = ref({ name: '', slug: '', description: '' });

const paymentSettings = ref({ platform_commission: 20, payout_minimum: 50 });
const securitySettings = ref({ password_min_length: 8, session_timeout: 120 });
const localizationSettings = ref({ default_language: 'en', enabled_languages: ['en', 'ar'] });

const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/admin/settings/categories');
    categories.value = res.data;
  } catch (e) { console.error(e); }
};

const fetchSettings = async () => {
  try {
    const res = await axios.get('/api/admin/settings/platform');
    if (res.data.payment) paymentSettings.value = { ...paymentSettings.value, ...res.data.payment };
    if (res.data.security) securitySettings.value = { ...securitySettings.value, ...res.data.security };
    if (res.data.localization) localizationSettings.value = { ...localizationSettings.value, ...res.data.localization };
  } catch (e) { console.error(e); }
};

const openCategoryModal = (cat = null) => {
  editingCategory.value = cat;
  categoryForm.value = cat ? { name: cat.name, slug: cat.slug, description: cat.description || '' } : { name: '', slug: '', description: '' };
  categoryModalVisible.value = true;
};

const saveCategory = async () => {
  try {
    if (editingCategory.value) {
      await axios.put(`/api/admin/settings/categories/${editingCategory.value.id}`, categoryForm.value);
      showSuccess('Category updated');
    } else {
      await axios.post('/api/admin/settings/categories', categoryForm.value);
      showSuccess('Category created');
    }
    categoryModalVisible.value = false;
    fetchCategories();
  } catch (e) {
    showError(e.response?.data?.message || 'Failed to save category');
  }
};

const deleteCategory = async (cat) => {
  const result = await confirmDelete(cat.name);
  if (!result.isConfirmed) return;
  try {
    await axios.delete(`/api/admin/settings/categories/${cat.id}`);
    showSuccess('Category deleted');
    fetchCategories();
  } catch (e) {
    showError(e.response?.data?.message || 'Cannot delete category');
  }
};

const savePaymentSettings = async () => {
  const result = await confirmUpdate('payment settings');
  if (!result.isConfirmed) return;
  try {
    await axios.put('/api/admin/settings/payment', paymentSettings.value);
    showSuccess('Payment settings saved');
  } catch (e) { showError('Failed to save'); }
};

const saveSecuritySettings = async () => {
  const result = await confirmUpdate('security settings');
  if (!result.isConfirmed) return;
  try {
    await axios.put('/api/admin/settings/security', securitySettings.value);
    showSuccess('Security settings saved');
  } catch (e) { showError('Failed to save'); }
};

const saveLocalizationSettings = async () => {
  const result = await confirmUpdate('localization settings');
  if (!result.isConfirmed) return;
  try {
    await axios.put('/api/admin/settings/localization', localizationSettings.value);
    showSuccess('Localization settings saved');
  } catch (e) { showError('Failed to save'); }
};

const handleLogout = async () => {
  await auth.logout();
  router.push('/login');
};

onMounted(() => {
  fetchCategories();
  fetchSettings();
});
</script>
