<template>
  <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 min-h-screen flex flex-col transition-all duration-300 z-50">
    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50">
        <router-link to="/" class="flex items-center gap-2 group">
             <div class="bg-purple-600 p-1.5 rounded-lg group-hover:bg-purple-500 transition-colors">
                 <LayoutDashboard class="w-5 h-5 text-white" />
             </div>
             <span class="font-bold text-slate-800 dark:text-white text-lg tracking-tight">NetLearn</span>
        </router-link>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto custom-scrollbar">
        <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $t('admin.sidebar.overview') }}</div>
        
        <router-link v-for="item in overviewItems" :key="item.path" :to="item.path" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
               ]">
                <component :is="item.icon" class="w-5 h-5" />
                {{ item.label }}
            </a>
        </router-link>

        <div class="mt-6 px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $t('admin.sidebar.management') }}</div>
        
        <router-link v-for="item in managementItems" :key="item.path" :to="item.path" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
               ]">
                <component :is="item.icon" class="w-5 h-5" />
                {{ item.label }}
            </a>
        </router-link>

        <div class="mt-6 px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $t('admin.sidebar.moderation') }}</div>
        
        <router-link v-for="item in moderationItems" :key="item.path" :to="item.path" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
               ]">
                <component :is="item.icon" class="w-5 h-5" />
                {{ item.label }}
            </a>
        </router-link>

        <div class="mt-6 px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $t('admin.sidebar.system') }}</div>
        
        <router-link v-for="item in systemItems" :key="item.path" :to="item.path" custom v-slot="{ href, navigate, isActive }">
            <a :href="href" @click="navigate" 
               :class="[
                 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                 isActive 
                   ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' 
                   : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
               ]">
                <component :is="item.icon" class="w-5 h-5" />
                {{ item.label }}
            </a>
        </router-link>
    </nav>

  </aside>

  <KeyboardHelpModal 
    :show="showHelpModal" 
    :shortcuts="shortcuts" 
    @close="showHelpModal = false" 
  />
</template>

<script setup>
import { 
    LayoutDashboard, 
    LayoutTemplate, 
    Users, 
    GraduationCap,
    BookOpen, 
    UserCheck,
    Star,
    MessageSquareMore,
    Tags,
    Settings,
} from 'lucide-vue-next';
import { useKeyboardShortcuts } from '../../composables/useKeyboardShortcuts';
import KeyboardHelpModal from './KeyboardHelpModal.vue';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { t } = useI18n();
const { showHelpModal, shortcuts } = useKeyboardShortcuts();

defineEmits(['logout']);

const overviewItems = computed(() => [
    { path: '/admin', label: t('admin.sidebar.dashboard'), icon: LayoutTemplate },
]);

const managementItems = computed(() => [
    { path: '/admin/users', label: t('admin.sidebar.users'), icon: Users },
    { path: '/admin/instructors', label: t('admin.sidebar.instructors'), icon: GraduationCap },
    { path: '/admin/courses', label: t('admin.sidebar.courses'), icon: BookOpen },
    { path: '/admin/enrollments', label: t('admin.sidebar.enrollments'), icon: UserCheck },
]);

const moderationItems = computed(() => [
    { path: '/admin/reviews', label: t('admin.sidebar.reviews'), icon: Star },
    { path: '/admin/qna', label: t('admin.sidebar.qna'), icon: MessageSquareMore },
    { path: '/admin/promo-codes', label: t('admin.sidebar.promo_codes'), icon: Tags },
]);

const systemItems = computed(() => [
    { path: '/admin/settings', label: t('admin.sidebar.settings'), icon: Settings },
]);
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
