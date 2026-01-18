<template>
  <footer class="bg-gray-900 text-white pt-16 pb-8 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <!-- Company -->
        <div>
          <h3 class="font-bold text-lg mb-4">{{ $t('common.footer.company') }}</h3>
          <ul class="space-y-2">
            <li><router-link to="/about" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.about') }}</router-link></li>
            <li><router-link to="/careers" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.careers') }}</router-link></li>
            <li><router-link to="/blog" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.blog') }}</router-link></li>
          </ul>
        </div>

        <!-- Community -->
        <div>
          <h3 class="font-bold text-lg mb-4">{{ $t('common.footer.community') }}</h3>
          <ul class="space-y-2">
            <li><router-link to="/instructor/signup" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.become_instructor') }}</router-link></li>
            <li><router-link to="/affiliate" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.affiliate') }}</router-link></li>
            <li><router-link to="/partners" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.partners') }}</router-link></li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h3 class="font-bold text-lg mb-4">{{ $t('common.footer.support') }}</h3>
          <ul class="space-y-2">
            <li><router-link to="/help" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.help') }}</router-link></li>
            <li><router-link to="/contact" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.contact') }}</router-link></li>
            <li><router-link to="/faq" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.faq') }}</router-link></li>
          </ul>
        </div>

        <!-- Legal -->
        <div>
          <h3 class="font-bold text-lg mb-4">{{ $t('common.footer.legal') }}</h3>
          <ul class="space-y-2">
            <li><router-link to="/terms" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.terms') }}</router-link></li>
            <li><router-link to="/privacy" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.privacy') }}</router-link></li>
            <li><router-link to="/cookie-settings" class="text-gray-400 hover:text-white transition-colors">{{ $t('common.footer.cookie') }}</router-link></li>
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
        <div class="mb-4 md:mb-0">
          <router-link to="/" class="text-2xl font-bold text-white">NetLearn</router-link>
        </div>
        <div class="text-gray-400 text-sm">
          {{ $t('common.footer.rights') }}
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
</script>
