<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  categories: { type: Array, default: () => [] }
})

const q = ref('') 

const parents = computed(() =>
  props.categories
    .filter(c => !c.parent_id)
    .sort((a, b) => a.name.localeCompare(b.name))
)

const childrenByParent = computed(() => {
  const map = new Map()
  props.categories
    .filter(c => !!c.parent_id)
    .forEach(c => {
      if (!map.has(c.parent_id)) map.set(c.parent_id, [])
      map.get(c.parent_id).push(c)
    })
  // sort children by name
  for (const [k, arr] of map.entries()) {
    arr.sort((a, b) => a.name.localeCompare(b.name))
    map.set(k, arr)
  }
  return map
})

/** Filtered tree (client-side search by name) */
const filteredParents = computed(() => {
  const term = q.value.trim().toLowerCase()
  if (!term) return parents.value

  // Keep parent if its name matches or any child matches
  return parents.value.filter(p => {
    const matchParent = p.name.toLowerCase().includes(term)
    const kids = childrenByParent.value.get(p.id) || []
    const matchChild = kids.some(k => k.name.toLowerCase().includes(term))
    return matchParent || matchChild
  })
})

function deleteCategory(id) {
  if (!confirm('Delete this category?')) return
  router.delete(route('categories.destroy', { category: id }), {
    preserveScroll: true
  })
}
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-6xl space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Categories</h1>
          <p class="text-sm text-gray-500">Manage parent and child categories (max two levels).</p>
        </div>
        <Link
          :href="route('categories.create')"
          class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition"
        >
          ＋ New Category
        </Link>
      </div>

      <!-- Search -->
      <div class="rounded-2xl border bg-white p-4 shadow-sm">
        <div class="flex items-center gap-3">
          <input
            v-model="q"
            type="text"
            placeholder="Search categories…"
            class="flex-1 rounded-xl border-gray-300 bg-white px-4 py-2.5 shadow-inner focus:border-blue-500 focus:ring-blue-500"
          />
          <button
            type="button"
            @click="q = ''"
            class="rounded-xl border px-4 py-2 text-sm hover:bg-gray-50"
            :disabled="!q"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Tree list -->
      <div v-if="filteredParents.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="parent in filteredParents"
          :key="parent.id"
          class="rounded-2xl border bg-white p-5 shadow-sm"
        >
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <h2 class="text-lg font-semibold text-gray-900">{{ parent.name }}</h2>
              <p class="text-xs text-gray-500">Parent category</p>
            </div>
            <div class="flex items-center gap-2">
              <Link
                :href="route('categories.show', { category: parent.id })"
                class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50"
              >
                View
              </Link>
              <Link
                :href="route('categories.edit', { category: parent.id })"
                class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50"
              >
                Edit
              </Link>
              <button
                type="button"
                @click="deleteCategory(parent.id)"
                class="rounded-lg bg-rose-600 px-3 py-1.5 text-sm text-white hover:bg-rose-700"
              >
                Delete
              </button>
            </div>
          </div>

          <!-- Children -->
          <div class="mt-4">
            <div class="text-xs font-medium text-gray-600 mb-2">Children</div>

            <div v-if="(childrenByParent.get(parent.id) || []).length" class="space-y-2">
              <div
                v-for="child in childrenByParent.get(parent.id)"
                :key="child.id"
                class="flex items-center justify-between rounded-xl border p-3 hover:bg-gray-50 transition"
              >
                <div class="text-sm font-medium text-gray-800">
                  {{ child.name }}
                </div>
                <div class="flex items-center gap-2">
                  <Link
                    :href="route('categories.show', { category: child.id })"
                    class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50"
                  >
                    View
                  </Link>
                  <Link
                    :href="route('categories.edit', { category: child.id })"
                    class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50"
                  >
                    Edit
                  </Link>
                  <button
                    type="button"
                    @click="deleteCategory(child.id)"
                    class="rounded-lg bg-rose-600 px-3 py-1.5 text-sm text-white hover:bg-rose-700"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="rounded-xl border border-dashed p-4 text-sm text-gray-500">
              No children for this parent.
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="rounded-2xl border bg-white p-12 text-center shadow-sm">
        <div class="mx-auto w-fit rounded-full bg-gray-50 p-4 ring-1 ring-gray-100">
          <span class="text-2xl">📂</span>
        </div>
        <h3 class="mt-4 text-lg font-semibold text-gray-900">No categories found</h3>
        <p class="mt-1 text-sm text-gray-600">Create your first category to get started.</p>
        <div class="mt-6">
          <Link
            :href="route('categories.create')"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm hover:bg-blue-700 active:scale-[.98] transition"
          >
            ＋ New Category
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
