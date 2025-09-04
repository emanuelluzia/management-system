<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, ref,onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useCategoryStatsStore } from '@/stores/useCategoryStatsStore'
const props = defineProps({
  stats: { type: Array, required: true },
  totals: { type: Object, required: true }
})

const statsStore = useCategoryStatsStore()

onMounted(() => {
  statsStore.setFromInertia({ stats: props.stats, totals: props.totals })
})

const rows = computed(() => statsStore.stats?.length ? statsStore.stats : props.stats)
const totals = computed(() => statsStore.totals ?? props.totals)

async function refresh() {
  await statsStore.fetch()
}


const q = ref('')
const sortBy = ref('name')         // name | total | pending | in_progress | completed
const sortDir = ref('asc')         // asc | desc
const showOnlyParents = ref(false) // optional toggle

const filtered = computed(() => {
  const term = q.value.trim().toLowerCase()
  const rows = props.stats.filter(r => {
    if (showOnlyParents.value && r.parent_id) return false
    if (!term) return true
    return r.name.toLowerCase().includes(term)
  })

  const keyMap = {
    name: 'name',
    total: 'tasks_total_count',
    pending: 'tasks_pending_count',
    in_progress: 'tasks_in_progress_count',
    completed: 'tasks_completed_count'
  }
  const key = keyMap[sortBy.value] || 'name'

  rows.sort((a, b) => {
    const A = a[key] ?? (key === 'name' ? '' : 0)
    const B = b[key] ?? (key === 'name' ? '' : 0)
    if (key === 'name') return sortDir.value === 'asc' ? A.localeCompare(B) : B.localeCompare(A)
    return sortDir.value === 'asc' ? A - B : B - A
  })

  return rows
})

function toggleSort(field) {
  if (sortBy.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDir.value = field === 'name' ? 'asc' : 'desc'
  }
}

function badge(color) {
  return `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ${color}`
}
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-6xl space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Category Statistics</h1>
          <button
            type="button"
            @click="refresh"
            :disabled="statsStore.loading"
            class="rounded-xl border px-3 py-2 text-sm hover:bg-gray-50 disabled:opacity-60"
          >
            {{ statsStore.loading ? 'Refreshing…' : 'Refresh' }}
          </button>
          <span v-if="statsStore.lastUpdated" class="ml-2 text-xs text-gray-500">Updated: {{ new Date(statsStore.lastUpdated).toLocaleTimeString() }}</span>

          <p class="text-sm text-gray-500">Aggregated counts by status, optimized at the backend.</p>
        </div>
        <Link :href="route('categories.index')" class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50">
          Back to list
        </Link>
      </div>

      <!-- Totals -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">Total tasks</div>
          <div class="mt-1 text-2xl font-semibold">{{ totals.total }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">Pending</div>
          <div class="mt-1 text-2xl font-semibold">{{ totals.pending }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">In Progress</div>
          <div class="mt-1 text-2xl font-semibold">{{ totals.in_progress }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">Completed</div>
          <div class="mt-1 text-2xl font-semibold">{{ totals.completed }}</div>
        </div>
      </div>

      <!-- Controls -->
      <div class="rounded-2xl border bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex-1 flex items-center gap-2">
            <input
              v-model="q"
              type="text"
              placeholder="Search category…"
              class="flex-1 rounded-xl border-gray-300 bg-white px-4 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            />
            <button type="button" @click="q=''" class="rounded-xl border px-3 py-2 text-sm hover:bg-gray-50" :disabled="!q">Clear</button>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-xs text-gray-600">Show:</label>
            <select v-model="showOnlyParents" class="rounded-xl border-gray-300 bg-white px-3 py-2 shadow-inner focus:border-blue-500 focus:ring-blue-500">
              <option :value="false">Parents + children</option>
              <option :value="true">Parents only</option>
            </select>

            <label class="text-xs text-gray-600 ml-2">Sort by:</label>
            <select v-model="sortBy" class="rounded-xl border-gray-300 bg-white px-3 py-2 shadow-inner focus:border-blue-500 focus:ring-blue-500">
              <option value="name">Name</option>
              <option value="total">Total</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
            <button type="button" @click="sortDir = sortDir==='asc' ? 'desc' : 'asc'" class="rounded-xl border px-3 py-2 text-sm hover:bg-gray-50">
              {{ sortDir.toUpperCase() }}
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr class="text-left text-gray-600">
                <th class="px-4 py-3 font-medium cursor-pointer" @click="toggleSort('name')">Category</th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="toggleSort('pending')">Pending</th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="toggleSort('in_progress')">In Progress</th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="toggleSort('completed')">Completed</th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="toggleSort('total')">Total</th>
                <th class="px-4 py-3 font-medium text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="r in filtered" :key="r.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-900">
                    <span v-if="r.parent_id" class="text-gray-400">↳ </span>{{ r.name }}
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span :class="badge('bg-amber-50 text-amber-700 ring-amber-200')">{{ r.tasks_pending_count }}</span>
                </td>
                <td class="px-4 py-3">
                  <span :class="badge('bg-blue-50 text-blue-700 ring-blue-200')">{{ r.tasks_in_progress_count }}</span>
                </td>
                <td class="px-4 py-3">
                  <span :class="badge('bg-emerald-50 text-emerald-700 ring-emerald-200')">{{ r.tasks_completed_count }}</span>
                </td>
                <td class="px-4 py-3 font-semibold">{{ r.tasks_total_count }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('categories.show', { category: r.id })" class="text-blue-600 hover:underline">View</Link>
                    <Link :href="route('categories.edit', { category: r.id })" class="text-indigo-600 hover:underline">Edit</Link>
                  </div>
                </td>
              </tr>

              <tr v-if="!filtered.length">
                <td colspan="6" class="px-4 py-10 text-center text-gray-500">No categories match the current filters.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
