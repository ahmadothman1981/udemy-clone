<template>
  <section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $t('home.featured.title') }}</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">{{ $t('home.featured.subtitle') }}</p>
      </div>
      
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div 
          v-for="(category, index) in categories" 
          :key="category.name"
          class="category-card group"
          :style="{ animationDelay: `${index * 0.1}s` }"
          @click="$router.push(`/?category=${category.slug}`)"
        >
          <div class="category-icon" :class="category.bgClass">
            <component :is="category.icon" class="w-8 h-8 text-white" />
          </div>
          <h3 class="font-semibold text-gray-800 group-hover:text-purple-600 transition-colors text-sm text-center">
            {{ category.name }}
          </h3>
          <p class="text-xs text-gray-500 text-center">{{ category.count }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, h } from 'vue';
import { useCourseStore } from '../stores/course';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const courseStore = useCourseStore();

// Icon components as functional components
const CodeIcon = () => h('svg', { 
  xmlns: 'http://www.w3.org/2000/svg', 
  class: 'w-8 h-8', 
  fill: 'none', 
  viewBox: '0 0 24 24', 
  stroke: 'currentColor' 
}, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' })
]);

const ChartIcon = () => h('svg', { 
  xmlns: 'http://www.w3.org/2000/svg', 
  class: 'w-8 h-8', 
  fill: 'none', 
  viewBox: '0 0 24 24', 
  stroke: 'currentColor' 
}, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
]);

const PaletteIcon = () => h('svg', { 
  xmlns: 'http://www.w3.org/2000/svg', 
  class: 'w-8 h-8', 
  fill: 'none', 
  viewBox: '0 0 24 24', 
  stroke: 'currentColor' 
}, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01' })
]);

const BriefcaseIcon = () => h('svg', { 
  xmlns: 'http://www.w3.org/2000/svg', 
  class: 'w-8 h-8', 
  fill: 'none', 
  viewBox: '0 0 24 24', 
  stroke: 'currentColor' 
}, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' })
]);

const CameraIcon = () => h('svg', { 
  xmlns: 'http://www.w3.org/2000/svg', 
  class: 'w-8 h-8', 
  fill: 'none', 
  viewBox: '0 0 24 24', 
  stroke: 'currentColor' 
}, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z' }),
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 13a3 3 0 11-6 0 3 3 0 016 0z' })
]);

const MusicIcon = () => h('svg', { 
  xmlns: 'http://www.w3.org/2000/svg', 
  class: 'w-8 h-8', 
  fill: 'none', 
  viewBox: '0 0 24 24', 
  stroke: 'currentColor' 
}, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3' })
]);

const categories = computed(() => [
  { name: t('home.categories.development'), slug: 'development', count: `1,200+ ${t('home.hero.courses_count_suffix')}`, icon: CodeIcon, bgClass: 'bg-gradient-to-br from-blue-500 to-blue-700' },
  { name: t('home.categories.data_science'), slug: 'data-science', count: `800+ ${t('home.hero.courses_count_suffix')}`, icon: ChartIcon, bgClass: 'bg-gradient-to-br from-green-500 to-emerald-700' },
  { name: t('home.categories.design'), slug: 'design', count: `650+ ${t('home.hero.courses_count_suffix')}`, icon: PaletteIcon, bgClass: 'bg-gradient-to-br from-pink-500 to-rose-700' },
  { name: t('home.categories.business'), slug: 'business', count: `900+ ${t('home.hero.courses_count_suffix')}`, icon: BriefcaseIcon, bgClass: 'bg-gradient-to-br from-amber-500 to-orange-700' },
  { name: t('home.categories.photography'), slug: 'photography', count: `400+ ${t('home.hero.courses_count_suffix')}`, icon: CameraIcon, bgClass: 'bg-gradient-to-br from-purple-500 to-violet-700' },
  { name: t('home.categories.music'), slug: 'music', count: `350+ ${t('home.hero.courses_count_suffix')}`, icon: MusicIcon, bgClass: 'bg-gradient-to-br from-red-500 to-rose-700' },
]);
</script>

<style scoped>
.category-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.5rem 1rem;
  background: white;
  border-radius: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid transparent;
  animation: fadeInUp 0.5s ease forwards;
  opacity: 0;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.category-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(139, 92, 246, 0.15);
  border-color: rgba(139, 92, 246, 0.2);
}

.category-icon {
  width: 4rem;
  height: 4rem;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  transition: transform 0.3s ease;
}

.category-card:hover .category-icon {
  transform: scale(1.1) rotate(5deg);
}
</style>
