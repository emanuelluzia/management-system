<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <!-- Top navbar -->
    <nav class="bg-white/90 backdrop-blur border-b">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-14 items-center justify-between">
          <!-- Brand -->
          <div class="flex items-center gap-3">
            <div class="h-7 w-7 rounded-xl bg-blue-600 text-white grid place-items-center font-bold">
              TM
            </div>
            <h1 class="text-sm font-semibold tracking-tight">Task Management System</h1>
          </div>

          <!-- Primary nav -->
          <div class="flex items-center gap-1">
            <Link
              :href="route('tasks.index')"
              class="nav-pill"
              :class="navClass(isActive('tasks.*') || urlStartsWith('/tasks'))"
              aria-current="page"
            >
              Tasks
            </Link>

            <Link
              :href="route('categories.index')"
              class="nav-pill"
              :class="navClass(isActive('categories.*') || urlStartsWith('/categories'))"
            >
              Categories
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Context sub-nav (changes by section) -->
    <div v-if="currentSection" class="bg-white border-b">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-12 items-center gap-2 text-sm">
          <!-- Tasks sub-nav -->
          <template v-if="currentSection === 'tasks'">
            <Link :href="route('tasks.index')" class="sub-pill" :class="subClass(isActive('tasks.index'))">List</Link>
            <Link :href="route('tasks.create')" class="sub-pill" :class="subClass(isActive('tasks.create'))">Create</Link>
          </template>

          <!-- Categories sub-nav -->
          <template v-else-if="currentSection === 'categories'">
            <Link :href="route('categories.index')" class="sub-pill" :class="subClass(isActive('categories.index'))">List</Link>
            <Link :href="route('categories.create')" class="sub-pill" :class="subClass(isActive('categories.create'))">Create</Link>
            <Link :href="route('categories.statistics')" class="sub-pill" :class="subClass(isActive('categories.statistics') || urlStartsWith('/categories/statistics'))">Statistics</Link>
          </template>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 sm:px-0">
        <slot />
      </div>
    </main>

    <!-- Global toasts -->
    <Toasts />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Toasts from '@/Components/Toasts.vue'

/** Checks if named route is current using Ziggy */
function isActive(pattern) {
  try {
    return route().current(pattern)
  } catch {
    return false
  }
}

/** Fallback using $page.url */
function urlStartsWith(prefix) {
  return typeof window !== 'undefined' && window?.__INERTIA__?.page?.url?.startsWith(prefix)
    ? true
    : false
}

/** Current section: 'tasks' | 'categories' | null */
const currentSection = computed(() => {
  if (isActive('tasks.*') || urlStartsWith('/tasks')) return 'tasks'
  if (isActive('categories.*') || urlStartsWith('/categories')) return 'categories'
  return null
})

/** Styling helpers */
function navClass(active) {
  return active
    ? 'bg-blue-600 text-white shadow-sm'
    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
}
function subClass(active) {
  return active
    ? 'bg-gray-900 text-white'
    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
}
</script>

<style scoped>
/* Primary nav pill */
.nav-pill { @apply inline-flex items-center rounded-xl px-3 py-1.5 text-sm transition; }
/* Sub-nav chip */
.sub-pill { @apply inline-flex items-center rounded-lg px-3 py-1 transition; }
</style>
