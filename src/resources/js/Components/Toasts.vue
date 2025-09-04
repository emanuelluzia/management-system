<script setup>
import { ref, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const visible = ref(false)
const message = ref('')
const type = ref('success')

function show(msg, t = 'success') {
  message.value = msg
  type.value = t
  visible.value = true
  setTimeout(() => { visible.value = false }, 4000) 
}

const flash = computed(() => page.props.flash || {})

// watch flash messages
watch(flash, (f) => {
  if (f?.success) show(f.success, 'success')
}, { immediate: true })
</script>

<template>
  <transition name="slide-fade">
    <div
      v-if="visible"
      class="fixed top-5 right-5 z-50 flex items-center gap-3 rounded-xl border px-5 py-3 shadow-lg"
      :class="type === 'success'
        ? 'bg-emerald-500 border-emerald-600 text-white'
        : 'bg-rose-500 border-rose-600 text-white'"
    >
      <!-- Icon -->
      <svg v-if="type==='success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>

      <!-- Message -->
      <span class="font-medium">{{ message }}</span>
    </div>
  </transition>
</template>

<style scoped>
/* Slide + fade animation */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px) translateX(10px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px) translateX(10px);
}
</style>
