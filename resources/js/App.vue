<template>
  <div class="min-h-screen bg-gray-50 text-gray-900 font-sans" :dir="$i18n.locale === 'ar' ? 'rtl' : 'ltr'">
    <router-view></router-view>
  </div>
</template>

<script setup>
// Layout handled in views or via meta layout property if needed
</script>
