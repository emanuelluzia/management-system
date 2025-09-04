<script setup>
import { computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  category: { type: Object, required: true },
  tasks: { type: Object, default: null },
  stats: { type: Object, default: null }
})

/** Normalize possible shapes for single resource */
const categoryObj = computed(() => props.category?.data ?? props.category)
/** Soft-deleted flag (if you pass deleted_at in category) */
const isDeleted = computed(() => !!categoryObj.value?.deleted_at)

/** Children (already loaded by controller via ->load('children')) */
const children = computed(() => (categoryObj.value?.children ?? []).slice().sort((a,b) => a.name.localeCompare(b.name)))

/** Pagination links fallback for tasks list */
const taskLinks = computed(() => {
  if (!props.tasks) return []
  if (Array.isArray(props.tasks.links)) return props.tasks.links
  if (Array.isArray(props.tasks.meta?.links)) return props.tasks.meta.links
  return []
})

/** Actions */
const restoreForm = useForm({})
function restoreCategory() {
  const id = categoryObj.value?.id
  if (!id) return
  restoreForm.post(route('categories.restore', id), { preserveScroll: true })
}

function deleteCategory() {
  const id = categoryObj.value?.id
  if (!id) return
  if (!confirm('Delete this category?')) return
  router.delete(route('categories.destroy', { category: id }), { preserveScroll: true })
}

/** Small helpers */
function fmtDate(iso) {
  if (!iso) return '—'
  const d = new Date(iso)
  return Number.isNaN(d.getTime()) ? '—' : d.toLocaleString()
}

function badge(color) {
  return `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ${color}`
}
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-6xl space-y-8">
      <!-- Header -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">{{ categoryObj.name }}</h1>
            <span
              v-if="isDeleted"
              class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200"
            >Deleted</span>
          </div>
          <p class="text-sm text-gray-500">Category details, hierarchy and related tasks.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('categories.index')" class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50">Back</Link>
          <Link
            :href="route('categories.edit', { category: categoryObj.id })"
            class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50"
          >
            Edit
          </Link>

          <button
            v-if="!isDeleted"
            type="button"
            @click="deleteCategory"
            class="rounded-xl bg-rose-600 px-4 py-2 text-white shadow-sm hover:bg-rose-700 active:scale-[.98] transition"
          >
            Delete
          </button>

          <button
            v-else
            type="button"
            @click="restoreCategory"
            class="rounded-xl bg-amber-600 px-4 py-2 text-white shadow-sm hover:bg-amber-700 active:scale-[.98] transition"
          >
            Restore
          </button>
        </div>
      </div>

      <!-- Info card -->
      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Column 1: hierarchy -->
          <div class="space-y-4">
            <div>
              <div class="text-xs font-medium text-gray-500">Parent</div>
              <div class="mt-1 text-gray-900">
                {{ categoryObj.parent?.name ?? '—' }}
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-gray-500">Children</div>
              <div class="mt-2 space-y-2">
                <div v-if="children.length === 0" class="text-gray-500 text-sm">No children.</div>
                <div v-else
                     v-for="child in children"
                     :key="child.id"
                     class="flex items-center justify-between rounded-xl border p-3 hover:bg-gray-50 transition"
                >
                  <div class="text-sm font-medium text-gray-800">
                    {{ child.name }}
                  </div>
                  <div class="flex items-center gap-2">
                    <Link :href="route('categories.show', { category: child.id })" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50">View</Link>
                    <Link :href="route('categories.edit', { category: child.id })" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50">Edit</Link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Column 2: meta -->
          <div class="space-y-4">
            <div>
              <div class="text-xs font-medium text-gray-500">Created at</div>
              <div class="mt-1 text-gray-900">{{ fmtDate(categoryObj.created_at) }}</div>
            </div>
            <div>
              <div class="text-xs font-medium text-gray-500">Updated at</div>
              <div class="mt-1 text-gray-900">{{ fmtDate(categoryObj.updated_at) }}</div>
            </div>
            <div>
              <div class="text-xs font-medium text-gray-500">Deleted at</div>
              <div class="mt-1 text-gray-900">{{ fmtDate(categoryObj.deleted_at) }}</div>
            </div>
          </div>

          <!-- Column 3: stats (optional) -->
          <div class="space-y-4">
            <div class="text-xs font-medium text-gray-500">Task stats</div>
            <div class="grid grid-cols-2 gap-3">
              <div class="rounded-xl border p-3">
                <div class="text-xs text-gray-500">Total</div>
                <div class="mt-1 text-lg font-semibold">
                  {{ props.stats?.tasks_total_count ?? '—' }}
                </div>
              </div>
              <div class="rounded-xl border p-3">
                <div class="text-xs text-gray-500">Pending</div>
                <div class="mt-1 text-lg font-semibold">
                  {{ props.stats?.tasks_pending_count ?? '—' }}
                </div>
              </div>
              <div class="rounded-xl border p-3">
                <div class="text-xs text-gray-500">In Progress</div>
                <div class="mt-1 text-lg font-semibold">
                  {{ props.stats?.tasks_in_progress_count ?? '—' }}
                </div>
              </div>
              <div class="rounded-xl border p-3">
                <div class="text-xs text-gray-500">Completed</div>
                <div class="mt-1 text-lg font-semibold">
                  {{ props.stats?.tasks_completed_count ?? '—' }}
                </div>
              </div>
            </div>
            <p v-if="!props.stats" class="text-xs text-gray-500">
              Tip: pass <code>stats</code> from the controller to show per-status counts.
            </p>
          </div>
        </div>
      </div>

      <!-- Tasks table (optional, only if you pass `tasks`) -->
      <div v-if="props.tasks" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Tasks in this category</h2>
          <span class="text-sm text-gray-500">
            {{ Array.isArray(props.tasks.data) ? props.tasks.data.length : '—' }} items on this page
          </span>
        </div>

        <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr class="text-left text-gray-600">
                  <th class="px-4 py-3 font-medium">Title</th>
                  <th class="px-4 py-3 font-medium">Status</th>
                  <th class="px-4 py-3 font-medium">Priority</th>
                  <th class="px-4 py-3 font-medium">Due date</th>
                  <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr
                  v-for="t in props.tasks.data"
                  :key="t.id"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-4 py-3">
                    <div class="font-medium" :class="t.deleted_at ? 'line-through text-gray-400' : 'text-gray-900'">
                      {{ t.title }}
                    </div>
                    <div class="text-xs text-gray-500 truncate max-w-[60ch]">{{ t.description }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <span :class="badge(t.status==='completed' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : t.status==='in_progress' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-amber-50 text-amber-700 ring-amber-200')">
                      {{ t.status === 'in_progress' ? 'In Progress' : (t.status === 'completed' ? 'Completed' : 'Pending') }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <span :class="badge(t.priority==='high' ? 'bg-rose-50 text-rose-700 ring-rose-200' : t.priority==='medium' ? 'bg-orange-50 text-orange-700 ring-orange-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200')">
                      {{ t.priority === 'high' ? 'High' : (t.priority === 'medium' ? 'Medium' : 'Low') }}
                    </span>
                  </td>
                  <td class="px-4 py-3">{{ t.due_date_formatted ?? '—' }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <Link :href="route('tasks.show', { task: t.id })" class="text-blue-600 hover:underline">View</Link>
                      <Link :href="route('tasks.edit', { task: t.id })" class="text-indigo-600 hover:underline">Edit</Link>
                    </div>
                  </td>
                </tr>

                <tr v-if="!props.tasks.data?.length">
                  <td colspan="5" class="px-4 py-10 text-center text-gray-500">No tasks in this category.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex flex-wrap items-center gap-2">
          <template v-for="link in taskLinks" :key="(link?.label ?? '') + (link?.url ?? '')">
            <span v-if="!link?.url" class="px-3 py-1 rounded-xl border text-sm text-gray-400" v-html="link?.label" />
            <Link v-else :href="link.url" class="px-3 py-1 rounded-xl border text-sm hover:bg-gray-50" :class="{ 'bg-blue-600 text-white border-blue-600': link.active }" v-html="link.label" />
          </template>
        </div>
      </div>

      <!-- Restore / Danger sections -->
      <div v-if="isDeleted" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-amber-900">This category is deleted</h2>
            <p class="text-xs text-amber-800">You can restore it to make it active again.</p>
          </div>
          <button type="button" @click="restoreCategory" class="rounded-xl bg-amber-600 px-4 py-2 text-white shadow-sm hover:bg-amber-700 active:scale-[.98] transition">
            Restore category
          </button>
        </div>
      </div>

      <div v-else class="rounded-2xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-rose-800">Danger zone</h2>
            <p class="text-xs text-rose-700">Deleting a category will soft-delete it.</p>
          </div>
          <button type="button" @click="deleteCategory" class="rounded-xl bg-rose-600 px-4 py-2 text-white shadow-sm hover:bg-rose-700 active:scale-[.98] transition">
            Delete category
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
