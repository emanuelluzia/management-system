<script setup>
import { computed, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useTaskFiltersStore } from '@/stores/useTaskFiltersStore'

/** Props from controller:
 * - tasks: paginator wrapped in TaskResource::collection($paginator)
 * - filters: {status, priority, category_id, search, due_from, due_to, with}
 * - sorting: {sort_by, sort_dir}
 * - statistics: { total, pending, inProgress, completed, overdue }
 */
const props = defineProps({
  tasks: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({}) },
  statistics: {
    type: Object,
    default: () => ({ total: 0, pending: 0, inProgress: 0, completed: 0, overdue: 0 })
  }
})

/** Pinia store for filters/sorting (persisted in localStorage) */
const filtersStore = useTaskFiltersStore()

onMounted(() => {
  // 1) load previous filters from localStorage
  filtersStore.load()
  // 2) merge with server-side filters (when landing from a link with query string)
  filtersStore.setFromInertia({ filters: props.filters, sorting: props.sorting })
})

/** Use the store directly as our reactive source */
const local = filtersStore

/** Apply filters by GET navigation (Inertia) */
function applyFilters(replace = true) {
  filtersStore.save()
  router.get(route('tasks.index'), filtersStore.toQuery(), {
    preserveScroll: true,
    preserveState: true,
    replace
  })
}

/** Clear filters and re-apply (resets to defaults) */
function resetFilters() {
  filtersStore.resetAll()
  applyFilters(false)
}

/** Toggle/set sorting */
function setSort(field) {
  if (filtersStore.sort_by === field) {
    filtersStore.sort_dir = filtersStore.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    filtersStore.sort_by = field
    filtersStore.sort_dir = field === 'created_at' ? 'desc' : 'asc'
  }
  applyFilters()
}

/** Restore & Delete actions */
const restoreForm = useForm({})
function restoreTask(id) {
  restoreForm.post(route('tasks.restore', id), { preserveScroll: true })
}
function deleteTask(id) {
  if (!confirm('Delete this task?')) return
  router.delete(route('tasks.destroy', { task: id }), { preserveScroll: true })
}

/** UI helpers */
function statusBadgeClass(s) {
  switch (s) {
    case 'pending': return 'bg-amber-50 text-amber-700 ring-amber-200'
    case 'in_progress': return 'bg-blue-50 text-blue-700 ring-blue-200'
    case 'completed': return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
    default: return 'bg-gray-50 text-gray-700 ring-gray-200'
  }
}
function priorityBadgeClass(p) {
  switch (p) {
    case 'low': return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
    case 'medium': return 'bg-orange-50 text-orange-700 ring-orange-200'
    case 'high': return 'bg-rose-50 text-rose-700 ring-rose-200'
    default: return 'bg-gray-50 text-gray-700 ring-gray-200'
  }
}
function badge(base) {
  return `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ${base}`
}

/** Pagination links helper (supports paginator links or meta.links) */
const paginationLinks = computed(() => {
  if (Array.isArray(props.tasks?.links)) return props.tasks.links
  if (Array.isArray(props.tasks?.meta?.links)) return props.tasks.meta.links
  return []
})
</script>

<template>
  <AppLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Tasks</h1>
          <p class="text-sm text-gray-500">Manage tasks, filters and sorting.</p>
        </div>
        <Link
          :href="route('tasks.create')"
          class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition"
        >
          ＋ New Task
        </Link>
      </div>

      <!-- Stats cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">Total</div>
          <div class="mt-1 text-2xl font-semibold">{{ statistics.total }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">Pending</div>
          <div class="mt-1 text-2xl font-semibold">{{ statistics.pending }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">In Progress</div>
          <div class="mt-1 text-2xl font-semibold">{{ statistics.inProgress }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">Completed</div>
          <div class="mt-1 text-2xl font-semibold">{{ statistics.completed }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-gray-500">Overdue</div>
          <div class="mt-1 text-2xl font-semibold">{{ statistics.overdue }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-2xl border bg-white p-5 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Status -->
          <div>
            <label class="mb-1 block text-sm text-gray-600">Status</label>
            <select
              v-model="local.status"
              class="w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          <!-- Priority -->
          <div>
            <label class="mb-1 block text-sm text-gray-600">Priority</label>
            <select
              v-model="local.priority"
              class="w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>

          <!-- Category (ID or you can change to a select later) -->
          <div>
            <label class="mb-1 block text-sm text-gray-600">Category (ID)</label>
            <input
              v-model="local.category_id"
              type="number"
              min="1"
              placeholder="Optional"
              class="w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <!-- Due from -->
          <div>
            <label class="mb-1 block text-sm text-gray-600">Due from</label>
            <input
              v-model="local.due_from"
              type="date"
              class="w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <!-- Due to -->
          <div>
            <label class="mb-1 block text-sm text-gray-600">Due to</label>
            <input
              v-model="local.due_to"
              type="date"
              class="w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <!-- Trashed -->
          <div>
            <label class="mb-1 block text-sm text-gray-600">Show</label>
            <select
              v-model="local.with"
              class="w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Active only</option>
              <option value="with_trashed">Active + deleted</option>
              <option value="only_trashed">Deleted only</option>
            </select>
          </div>
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <!-- Search -->
          <div class="flex-1">
            <label class="sr-only">Search</label>
            <input
              v-model="local.search"
              placeholder="Search by title or description…"
              class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Sort by:</label>
            <select
              v-model="local.sort_by"
              class="rounded-xl border-gray-300 bg-white px-3 py-2 shadow-inner focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="created_at">Created at</option>
              <option value="due_date">Due date</option>
              <option value="priority">Priority</option>
              <option value="status">Status</option>
              <option value="title">Title</option>
            </select>
            <button
              type="button"
              @click="setSort(local.sort_by)"
              class="rounded-xl border px-3 py-2 text-sm hover:bg-gray-50"
              :title="local.sort_dir.toUpperCase()"
            >
              {{ local.sort_dir === 'asc' ? 'Asc' : 'Desc' }}
            </button>

            <button
              type="button"
              @click="applyFilters()"
              class="rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition"
            >
              Search
            </button>

            <button
              type="button"
              @click="resetFilters"
              class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
              <tr>
                <th class="px-4 py-3 font-medium">Title</th>
                <th class="px-4 py-3 font-medium">Category</th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="setSort('status')">Status</th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="setSort('priority')">Priority</th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="setSort('due_date')">
                  Due date
                  <span v-if="local.sort_by==='due_date'">({{ local.sort_dir }})</span>
                </th>
                <th class="px-4 py-3 font-medium cursor-pointer" @click="setSort('created_at')">
                  Created at
                  <span v-if="local.sort_by==='created_at'">({{ local.sort_dir }})</span>
                </th>
                <th class="px-4 py-3 font-medium text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr
                v-for="task in props.tasks.data"
                :key="task.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3">
                  <div class="font-medium" :class="task.deleted_at ? 'line-through text-gray-400' : 'text-gray-900'">
                    {{ task.title }}
                  </div>
                  <div class="text-xs text-gray-500 truncate max-w-[60ch]">{{ task.description }}</div>
                </td>
                <td class="px-4 py-3">
                  <span v-if="task.category">{{ task.category.name }}</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-4 py-3">
                  <span :class="badge(statusBadgeClass(task.status))">
                    {{ task.status === 'in_progress' ? 'In Progress' : (task.status === 'completed' ? 'Completed' : 'Pending') }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span :class="badge(priorityBadgeClass(task.priority))">
                    {{ task.priority === 'high' ? 'High' : (task.priority === 'medium' ? 'Medium' : 'Low') }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <span>{{ task.due_date_formatted ?? '—' }}</span>
                    <span v-if="task.is_overdue" class="text-xs px-2 py-0.5 rounded bg-rose-100 text-rose-700">Overdue</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span>{{ task.created_at ? new Date(task.created_at).toLocaleDateString() : '—' }}</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('tasks.show', { task: task.id })" class="text-blue-600 hover:underline">View</Link>
                    <Link :href="route('tasks.edit', { task: task.id })" class="text-indigo-600 hover:underline">Edit</Link>

                    <button
                      v-if="!task.deleted_at"
                      @click="deleteTask(task.id)"
                      class="text-rose-600 hover:underline"
                    >
                      Delete
                    </button>

                    <button
                      v-else
                      @click="restoreTask(task.id)"
                      class="text-amber-700 hover:underline"
                    >
                      Restore
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!props.tasks.data?.length">
                <td colspan="7" class="px-4 py-10 text-center text-gray-500">No tasks found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex flex-wrap items-center gap-2">
        <template v-for="link in paginationLinks" :key="(link?.label ?? '') + (link?.url ?? '')">
          <span
            v-if="!link?.url"
            class="px-3 py-1 rounded-xl border text-sm text-gray-400"
            v-html="link?.label"
          />
          <Link
            v-else
            :href="link.url"
            class="px-3 py-1 rounded-xl border text-sm hover:bg-gray-50"
            :class="{ 'bg-blue-600 text-white border-blue-600': link.active }"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </AppLayout>
</template>
