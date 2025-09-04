<script setup>
import { computed, watch, nextTick } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

/** Props */
const props = defineProps({
  task: { type: Object, default: null },          // plain object or { data: {} }
  categories: { type: Array, default: () => [] }  // [{id, name, parent_id}]
})

/** Normalize single resource shape */
const taskObj = computed(() => props.task?.data ?? props.task ?? null)
const isEdit = computed(() => !!taskObj.value?.id)
const formKey = computed(() => taskObj.value?.id ?? 'create')

/** Helpers */
function normalizeDate(d) {
  if (!d) return ''
  if (/^\d{4}-\d{2}-\d{2}$/.test(d)) return d
  const x = new Date(d)
  if (Number.isNaN(x.getTime())) return ''
  const y = x.getFullYear(), m = String(x.getMonth() + 1).padStart(2, '0'), dd = String(x.getDate()).padStart(2, '0')
  return `${y}-${m}-${dd}`
}

/** Inertia form */
const form = useForm({
  title: '',
  description: '',
  status: 'pending',   // pending | in_progress | completed
  priority: 'medium',  // low | medium | high
  due_date: '',
  category_id: ''      // keep as string in the UI (select)
})

/** Validation UX helpers */
function invalidClass(field) {
  return form.errors[field]
    ? 'ring-2 ring-rose-300 border-rose-300 focus:border-rose-400 focus:ring-rose-400'
    : ''
}
function clearFieldError(field) {
  if (form.errors[field]) form.clearErrors(field)
}
function scrollToFirstError() {
  nextTick(() => {
    const el = document.querySelector('[aria-invalid="true"]')
    if (el?.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    if (el?.focus) el.focus({ preventScroll: true })
  })
}

/** Sync form when props change (edit/create) */
watch(() => taskObj.value, (t) => {
  form.title       = t?.title ?? ''
  form.description = t?.description ?? ''
  form.status      = t?.status ?? 'pending'
  form.priority    = t?.priority ?? 'medium'
  form.due_date    = normalizeDate(t?.due_date)
  form.category_id = t?.category?.id != null ? String(t.category.id) : ''
}, { immediate: true })

/** Nested category select (2 levels: Parent › Child) */
const orderedCategories = computed(() => {
  const parents = props.categories.filter(c => !c.parent_id).sort((a,b) => a.name.localeCompare(b.name))
  const byParent = new Map()
  props.categories.filter(c => !!c.parent_id).forEach(c => {
    if (!byParent.has(c.parent_id)) byParent.set(c.parent_id, [])
    byParent.get(c.parent_id).push(c)
  })
  for (const [k, arr] of byParent.entries()) arr.sort((a,b) => a.name.localeCompare(b.name))

  const rows = []
  parents.forEach(p => {
    rows.push({ id: String(p.id), label: p.name })
    ;(byParent.get(p.id) || []).forEach(k => rows.push({ id: String(k.id), label: `${p.name} › ${k.name}` }))
  })
  return rows
})

/** Submit / Delete */
function handleSubmit() {
  if (isEdit.value) {
    const id = taskObj.value.id
    const payload = { ...form.data() }
    payload.category_id = payload.category_id === '' ? null : Number(payload.category_id)
    form.put(route('tasks.update', { task: id }), {
      preserveScroll: true,
      data: payload,
      onError: () => scrollToFirstError()
    })
  } else {
    const payload = { ...form.data() }
    payload.category_id = payload.category_id === '' ? null : Number(payload.category_id)
    form.post(route('tasks.store'), {
      preserveScroll: true,
      data: payload,
      onError: () => scrollToFirstError()
    })
  }
}

function handleDelete() {
  const id = taskObj.value?.id
  if (!id) return
  if (!confirm('Delete this task?')) return
  router.delete(route('tasks.destroy', { task: id }), { preserveScroll: true })
}

/** Options */
const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' }
]
const priorityOptions = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' }
]
</script>

<template>
  <AppLayout>
    <div :key="formKey" class="mx-auto max-w-5xl space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">
            {{ isEdit ? 'Edit Task' : 'Create Task' }}
          </h1>
          <p class="text-sm text-gray-500">
            {{ isEdit ? 'Update task details and save changes.' : 'Fill the form to create a new task.' }}
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link :href="route('tasks.index')" class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50">Back</Link>
          <button
            type="button"
            @click="handleSubmit"
            :disabled="form.processing"
            class="relative rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition disabled:opacity-60"
          >
            <span v-if="form.processing">Saving…</span>
            <span v-else>{{ isEdit ? 'Save changes' : 'Create task' }}</span>
            <svg v-if="form.processing" class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Form Card -->
      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-6">
          <!-- Title -->
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
            <input
              v-model="form.title"
              @input="clearFieldError('title')"
              :class="['w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('title')]"
              aria-invalid="!!form.errors.title"
              type="text"
              placeholder="e.g. Implement authentication"
            />
            <div v-if="form.errors.title" class="mt-1 text-sm text-rose-600">{{ form.errors.title }}</div>
          </div>

          <!-- Description -->
          <div>
            <div class="flex items-center justify-between">
              <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
              <span class="text-xs text-gray-400">Optional</span>
            </div>
            <textarea
              v-model="form.description"
              @input="clearFieldError('description')"
              :class="['w-full resize-y rounded-xl border-gray-300 bg-white px-4 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('description')]"
              aria-invalid="!!form.errors.description"
              rows="4"
              placeholder="Add more context or steps to complete this task…"
            />
            <div v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</div>
          </div>

          <!-- Grid: Status / Priority / Due date / Category -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
              <select
                v-model="form.status"
                @change="clearFieldError('status')"
                :class="['w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('status')]"
                aria-invalid="!!form.errors.status"
              >
                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
              <div v-if="form.errors.status" class="mt-1 text-sm text-rose-600">{{ form.errors.status }}</div>
            </div>

            <!-- Priority -->
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Priority</label>
              <select
                v-model="form.priority"
                @change="clearFieldError('priority')"
                :class="['w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('priority')]"
                aria-invalid="!!form.errors.priority"
              >
                <option v-for="opt in priorityOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
              <div v-if="form.errors.priority" class="mt-1 text-sm text-rose-600">{{ form.errors.priority }}</div>
            </div>

            <!-- Due date -->
            <div>
              <div class="flex items-center justify-between">
                <label class="mb-1 block text-sm font-medium text-gray-700">Due date</label>
                <span class="text-xs text-gray-400">Optional</span>
              </div>
              <input
                v-model="form.due_date"
                @input="clearFieldError('due_date')"
                :class="['w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('due_date')]"
                aria-invalid="!!form.errors.due_date"
                type="date"
              />
              <div v-if="form.errors.due_date" class="mt-1 text-sm text-rose-600">{{ form.errors.due_date }}</div>
            </div>

            <!-- Category (nested) -->
            <div>
              <div class="flex items-center justify-between">
                <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                <span class="text-xs text-gray-400">Optional</span>
              </div>
              <select
                v-model="form.category_id"
                @change="clearFieldError('category_id')"
                :class="['w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('category_id')]"
                aria-invalid="!!form.errors.category_id"
              >
                <option value="">Select a category</option>
                <option v-for="row in orderedCategories" :key="row.id" :value="row.id">{{ row.label }}</option>
              </select>
              <div v-if="form.errors.category_id" class="mt-1 text-sm text-rose-600">{{ form.errors.category_id }}</div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <Link :href="route('tasks.index')" class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50">Cancel</Link>
            <button
              type="button"
              @click="handleSubmit"
              :disabled="form.processing"
              class="relative rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition disabled:opacity-60"
            >
              <span v-if="form.processing">Saving…</span>
              <span v-else>{{ isEdit ? 'Save changes' : 'Create task' }}</span>
              <svg v-if="form.processing" class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div v-if="isEdit" class="rounded-2xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-rose-800">Danger zone</h2>
            <p class="text-xs text-rose-700">Deleting a task will soft-delete it. You can restore it later.</p>
          </div>
          <button
            type="button"
            @click="handleDelete"
            class="rounded-xl bg-rose-600 px-4 py-2 text-white shadow-sm hover:bg-rose-700 active:scale-[.98] transition"
          >
            Delete task
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
