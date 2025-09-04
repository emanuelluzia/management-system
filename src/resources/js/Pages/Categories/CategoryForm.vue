<script setup>
import { computed, watch, nextTick } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  category: { type: Object, default: null },
  rootCategories: { type: Array, default: () => [] }
})

const categoryObj = computed(() => props.category?.data ?? props.category ?? null)
const isEdit = computed(() => !!categoryObj.value?.id)
const formKey = computed(() => categoryObj.value?.id ?? 'create')

/** Inertia form */
const form = useForm({
  name: '',
  parent_id: '' // '' (root) or String(id)
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

/** Sync form on props change */
watch(() => categoryObj.value, (c) => {
  form.name = c?.name ?? ''
  form.parent_id = c?.parent_id != null ? String(c.parent_id) : ''
}, { immediate: true })

/** Parent options (roots only) */
const parentOptions = computed(() =>
  (props.rootCategories || [])
    .slice()
    .sort((a, b) => a.name.localeCompare(b.name))
    .map(c => ({ value: String(c.id), label: c.name }))
)

/** Submit / Delete */
function handleSubmit() {
  if (isEdit.value) {
    const id = categoryObj.value.id
    const payload = { ...form.data() }
    payload.parent_id = payload.parent_id === '' ? null : Number(payload.parent_id)
    form.put(route('categories.update', { category: id }), {
      preserveScroll: true,
      data: payload,
      onError: () => scrollToFirstError()
    })
  } else {
    const payload = { ...form.data() }
    payload.parent_id = payload.parent_id === '' ? null : Number(payload.parent_id)
    form.post(route('categories.store'), {
      preserveScroll: true,
      data: payload,
      onError: () => scrollToFirstError()
    })
  }
}

function handleDelete() {
  const id = categoryObj.value?.id
  if (!id) return
  if (!confirm('Delete this category?')) return
  router.delete(route('categories.destroy', { category: id }), { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <div :key="formKey" class="mx-auto max-w-4xl space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">
            {{ isEdit ? 'Edit Category' : 'Create Category' }}
          </h1>
          <p class="text-sm text-gray-500">
            {{ isEdit ? 'Update category details and save changes.' : 'Fill the form to create a new category.' }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('categories.index')" class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50">Back</Link>
          <button
            type="button"
            @click="handleSubmit"
            :disabled="form.processing"
            class="relative rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition disabled:opacity-60"
          >
            <span v-if="form.processing">Saving…</span>
            <span v-else>{{ isEdit ? 'Save changes' : 'Create category' }}</span>
            <svg v-if="form.processing" class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Form card -->
      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-6">
          <!-- Name -->
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
            <input
              v-model="form.name"
              @input="clearFieldError('name')"
              :class="['w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('name')]"
              aria-invalid="!!form.errors.name"
              type="text"
              placeholder="e.g. Product"
            />
            <div v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</div>
          </div>

          <!-- Parent (root only) -->
          <div>
            <div class="flex items-center justify-between">
              <label class="mb-1 block text-sm font-medium text-gray-700">Parent category</label>
              <span class="text-xs text-gray-400">Optional</span>
            </div>
            <select
              v-model="form.parent_id"
              @change="clearFieldError('parent_id')"
              :class="['w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500', invalidClass('parent_id')]"
              aria-invalid="!!form.errors.parent_id"
            >
              <option value="">No parent (root)</option>
              <option v-for="opt in parentOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
            <div v-if="form.errors.parent_id" class="mt-1 text-sm text-rose-600">{{ form.errors.parent_id }}</div>
            <p class="mt-2 text-xs text-gray-500">Only root categories are available as parent. This ensures a maximum depth of two levels.</p>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <Link :href="route('categories.index')" class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50">Cancel</Link>
            <button
              type="button"
              @click="handleSubmit"
              :disabled="form.processing"
              class="relative rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition disabled:opacity-60"
            >
              <span v-if="form.processing">Saving…</span>
              <span v-else>{{ isEdit ? 'Save changes' : 'Create category' }}</span>
              <svg v-if="form.processing" class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Danger zone (only edit) -->
      <div v-if="isEdit" class="rounded-2xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-rose-800">Danger zone</h2>
            <p class="text-xs text-rose-700">Deleting a category will soft-delete it. You can restore it later if you implement a restore flow.</p>
          </div>
          <button
            type="button"
            @click="handleDelete"
            class="rounded-xl bg-rose-600 px-4 py-2 text-white shadow-sm hover:bg-rose-700 active:scale-[.98] transition"
          >
            Delete category
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
