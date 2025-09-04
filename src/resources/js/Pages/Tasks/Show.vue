<script setup>
import { computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  task: { type: Object, required: true } // TaskResource (single)
})

// Normalize possible shapes: {id,...} or {data:{...}}
const taskObj = computed(() => {
  return props.task?.data ?? props.task
})

const isDeleted = computed(() => !!taskObj.value?.deleted_at)
const isOverdue = computed(() => !!taskObj.value?.is_overdue)

function fmtDate(iso) {
  if (!iso) return '—'
  const d = new Date(iso)
  return Number.isNaN(d.getTime()) ? '—' : d.toLocaleString()
}

// Status / Priority badges
function statusBadgeClass(s) {
  switch (s) {
    case 'pending':     return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
    case 'in_progress': return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
    case 'completed':   return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
    default:            return 'bg-gray-50 text-gray-700 ring-1 ring-gray-200'
  }
}
function priorityBadgeClass(p) {
  switch (p) {
    case 'low':    return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
    case 'medium': return 'bg-orange-50 text-orange-700 ring-1 ring-orange-200'
    case 'high':   return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
    default:       return 'bg-gray-50 text-gray-700 ring-1 ring-gray-200'
  }
}

// Actions
const restoreForm = useForm({})
function restoreTask() {
  const id = taskObj.value?.id
  if (!id) return
  restoreForm.post(route('tasks.restore', id), { preserveScroll: true })
}
function deleteTask() {
  const id = taskObj.value?.id
  if (!id) return
  if (!confirm('Delete this task?')) return
  router.delete(route('tasks.destroy', { task: id }), { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-5xl space-y-8">
      <!-- Header -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">{{ taskObj.title }}</h1>
            <span v-if="isDeleted" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200">
              Deleted
            </span>
            <span v-if="isOverdue" class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700 ring-1 ring-rose-200">
              Overdue
            </span>
          </div>
          <p class="text-sm text-gray-500">Task details and quick actions.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('tasks.index')" class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50">Back</Link>
          <Link
            :href="route('tasks.edit', { task: taskObj.id })"
            class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50"
          >
            Edit
          </Link>

          <button
            v-if="!isDeleted"
            type="button"
            @click="deleteTask"
            class="rounded-xl bg-rose-600 px-4 py-2 text-white shadow-sm hover:bg-rose-700 active:scale-[.98] transition"
          >
            Delete
          </button>

          <button
            v-else
            type="button"
            @click="restoreTask"
            class="rounded-xl bg-amber-600 px-4 py-2 text-white shadow-sm hover:bg-amber-700 active:scale-[.98] transition"
          >
            Restore
          </button>
        </div>
      </div>

      <!-- Meta card -->
      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Column 1 -->
          <div class="space-y-4">
            <div>
              <div class="text-xs font-medium text-gray-500">Status</div>
              <div class="mt-1">
                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium" :class="statusBadgeClass(taskObj.status)">
                  {{ taskObj.status === 'in_progress' ? 'In Progress' : (taskObj.status === 'completed' ? 'Completed' : 'Pending') }}
                </span>
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-gray-500">Priority</div>
              <div class="mt-1">
                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium" :class="priorityBadgeClass(taskObj.priority)">
                  {{ taskObj.priority === 'high' ? 'High' : (taskObj.priority === 'medium' ? 'Medium' : 'Low') }}
                </span>
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-gray-500">Category</div>
              <div class="mt-1 text-gray-900">
                {{ taskObj.category?.name ?? '—' }}
              </div>
            </div>
          </div>

          <!-- Column 2 -->
          <div class="space-y-4">
            <div>
              <div class="text-xs font-medium text-gray-500">Due date</div>
              <div class="mt-1 text-gray-900">
                {{ taskObj.due_date_formatted ?? '—' }}
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-gray-500">Created at</div>
              <div class="mt-1 text-gray-900">
                {{ fmtDate(taskObj.created_at) }}
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-gray-500">Updated at</div>
              <div class="mt-1 text-gray-900">
                {{ fmtDate(taskObj.updated_at) }}
              </div>
            </div>
          </div>

          <!-- Column 3 -->
          <div class="space-y-4">
            <div>
              <div class="text-xs font-medium text-gray-500">Deleted at</div>
              <div class="mt-1 text-gray-900">
                {{ fmtDate(taskObj.deleted_at) }}
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-gray-500">Overdue</div>
              <div class="mt-1">
                <span
                  class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                  :class="isOverdue ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200'"
                >
                  {{ isOverdue ? 'Yes' : 'No' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="my-6 h-px w-full bg-gray-100"></div>

        <!-- Description -->
        <div>
          <div class="text-xs font-medium text-gray-500">Description</div>
          <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-800">
            {{ taskObj.description || '—' }}
          </p>
        </div>
      </div>

      <!-- Danger zone (if not already deleted, action is here too) -->
      <div v-if="!isDeleted" class="rounded-2xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-rose-800">Danger zone</h2>
            <p class="text-xs text-rose-700">
              Deleting a task moves it to the trash (soft delete). You can restore it later.
            </p>
          </div>
          <button
            type="button"
            @click="deleteTask"
            class="rounded-xl bg-rose-600 px-4 py-2 text-white shadow-sm hover:bg-rose-700 active:scale-[.98] transition"
          >
            Delete task
          </button>
        </div>
      </div>

      <!-- Restore block (only when deleted) -->
      <div v-else class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-amber-900">This task is deleted</h2>
            <p class="text-xs text-amber-800">You can restore it to make it active again.</p>
          </div>
          <button
            type="button"
            @click="restoreTask"
            class="rounded-xl bg-amber-600 px-4 py-2 text-white shadow-sm hover:bg-amber-700 active:scale-[.98] transition"
          >
            Restore task
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
