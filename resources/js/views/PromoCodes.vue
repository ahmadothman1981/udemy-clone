<template>
  <div class="bg-gray-50 min-h-screen flex font-inter">
      <AdminSidebar @logout="handleLogout" />
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
            <h1 class="text-xl font-bold text-slate-800">Promo Codes</h1>
            <div class="flex items-center gap-4">
                 <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-purple-200">
                     <Plus class="w-4 h-4" />
                     New Promo Code
                 </button>
                 <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300">
                     {{ auth.user?.name?.[0] || 'A' }}
                 </div>
            </div>
        </header>

        <div class="flex-1 overflow-auto p-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Code</th>
                                <th class="px-6 py-4 font-semibold">Discount</th>
                                <th class="px-6 py-4 font-semibold">Usage</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="promo in promoCodes" :key="promo.id" class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 font-mono tracking-wide">{{ promo.code }}</span>
                                        <span class="text-xs text-slate-500 truncate max-w-[200px]">{{ promo.description || 'No description' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <div class="flex items-center gap-2">
                                         <span :class="['p-1.5 rounded-lg', promo.discount_type === 'percentage' ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600']">
                                             <component :is="promo.discount_type === 'percentage' ? Percent : DollarSign" class="w-4 h-4" />
                                         </span>
                                         <span class="font-medium text-slate-700">
                                             {{ promo.discount_type === 'percentage' ? `${promo.discount_value}%` : `$${promo.discount_value}` }}
                                         </span>
                                     </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600">
                                        <span class="font-bold">{{ promo.used_count }}</span>
                                        <span class="text-slate-400" v-if="promo.max_uses"> / {{ promo.max_uses }}</span>
                                        uses
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5" v-if="promo.min_purchase > 0">
                                        Min. ${{ promo.min_purchase }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <span :class="['px-2.5 py-1 rounded-full text-xs font-medium border', isActive(promo) ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200']">
                                        {{ isActive(promo) ? 'Active' : 'Inactive' }}
                                     </span>
                                     <div class="text-[10px] text-slate-400 mt-1" v-if="promo.expires_at">
                                         Exp: {{ new Date(promo.expires_at).toLocaleDateString() }}
                                     </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openModal(promo)" class="p-2 hover:bg-slate-100 text-slate-500 hover:text-purple-600 rounded-lg transition-colors" title="Edit">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click="deletePromo(promo.id)" class="p-2 hover:bg-red-50 text-slate-500 hover:text-red-600 rounded-lg transition-colors" title="Delete">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="promoCodes.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                    No promo codes found. Create one to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </main>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" @click.self="closeModal">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
              <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                  <h3 class="font-bold text-slate-800">{{ isEditing ? 'Edit Promo Code' : 'New Promo Code' }}</h3>
                  <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                      <X class="w-5 h-5" />
                  </button>
              </div>
              
              <form @submit.prevent="savePromo" class="p-6 space-y-4">
                  <div>
                      <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Code</label>
                      <input v-model="form.code" type="text" required placeholder="SUMMER2026" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent uppercase font-mono" :disabled="isEditing">
                  </div>
                  
                  <div>
                      <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Description</label>
                      <input v-model="form.description" type="text" placeholder="Summer Sale Discount" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                       <div>
                          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Type</label>
                          <select v-model="form.discount_type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                              <option value="percentage">Percentage (%)</option>
                              <option value="fixed">Fixed Amount ($)</option>
                          </select>
                      </div>
                      <div>
                          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Value</label>
                          <input v-model.number="form.discount_value" type="number" min="0" step="0.01" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                      </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                       <div>
                          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Min. Purchase ($)</label>
                          <input v-model.number="form.min_purchase" type="number" min="0" step="0.01" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                      </div>
                      <div>
                          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Max Uses</label>
                          <input v-model.number="form.max_uses" type="number" min="1" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Unlimited">
                      </div>
                  </div>

                   <div>
                      <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Expires At</label>
                      <input v-model="form.expires_at" type="date" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                  </div>

                  <div class="flex items-center gap-2 pt-2">
                      <input v-model="form.active" type="checkbox" id="active" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                      <label for="active" class="text-sm text-slate-700 font-medium">Active</label>
                  </div>

                  <div class="pt-4 flex justify-end gap-3">
                      <button type="button" @click="closeModal" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg text-sm font-medium transition-colors">Cancel</button>
                      <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                          {{ isEditing ? 'Update Code' : 'Create Code' }}
                      </button>
                  </div>
              </form>
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
import { Plus, Percent, DollarSign, Edit2, Trash2, X } from 'lucide-vue-next';

const auth = useAuthStore();
const router = useRouter();

const promoCodes = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
    code: '',
    description: '',
    discount_type: 'percentage',
    discount_value: 0,
    min_purchase: 0,
    max_uses: null,
    expires_at: '',
    active: true
});

const resetForm = () => {
    form.value = {
        code: '',
        description: '',
        discount_type: 'percentage',
        discount_value: 0,
        min_purchase: 0,
        max_uses: null,
        expires_at: '',
        active: true
    };
    isEditing.value = false;
    editingId.value = null;
};

const fetchPromos = async () => {
    try {
        const res = await axios.get('/api/admin/promo-codes');
        promoCodes.value = res.data;
    } catch (e) {
        console.error(e);
    }
};

const isActive = (promo) => {
    if (!promo.active) return false;
    if (promo.expires_at && new Date(promo.expires_at) < new Date()) return false;
    if (promo.max_uses && promo.used_count >= promo.max_uses) return false;
    return true;
};

const openModal = (promo = null) => {
    if (promo) {
        isEditing.value = true;
        editingId.value = promo.id;
        form.value = { 
            ...promo,
            expires_at: promo.expires_at ? new Date(promo.expires_at).toISOString().split('T')[0] : ''
        };
    } else {
        resetForm();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(resetForm, 200);
};

const savePromo = async () => {
    try {
        if (isEditing.value) {
            const res = await axios.put(`/api/admin/promo-codes/${editingId.value}`, form.value);
            const index = promoCodes.value.findIndex(p => p.id === editingId.value);
            if (index !== -1) promoCodes.value[index] = res.data;
        } else {
            const res = await axios.post('/api/admin/promo-codes', form.value);
            promoCodes.value.unshift(res.data);
        }
        closeModal();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to save promo code');
    }
};

const deletePromo = async (id) => {
    if (!confirm('Are you sure you want to delete this promo code?')) return;
    try {
        await axios.delete(`/api/admin/promo-codes/${id}`);
        promoCodes.value = promoCodes.value.filter(p => p.id !== id);
    } catch (e) {
        alert('Failed to delete promo code');
    }
};

const handleLogout = () => {
    auth.logout();
    router.push('/login');
};

onMounted(() => {
    fetchPromos();
});
</script>
