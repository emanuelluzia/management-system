import { defineStore } from 'pinia'

const STORAGE_KEY = 'task_filters_v1'

export const useTaskFiltersStore = defineStore('taskFilters', {
  state: () => ({
    status: '',
    priority: '',
    category_id: '',
    search: '',
    due_from: '',
    due_to: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    with: '', // '', 'with_trashed', 'only_trashed'
  }),

  actions: {
    /** Load from localStorage */
    load() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY)
        if (!raw) return
        const data = JSON.parse(raw)
        Object.assign(this, data)
      } catch {}
    },

    /** Save to localStorage */
    save() {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        status: this.status,
        priority: this.priority,
        category_id: this.category_id,
        search: this.search,
        due_from: this.due_from,
        due_to: this.due_to,
        sort_by: this.sort_by,
        sort_dir: this.sort_dir,
        with: this.with,
      }))
    },

    /** Clear and persist */
    resetAll() {
      this.status = ''
      this.priority = ''
      this.category_id = ''
      this.search = ''
      this.due_from = ''
      this.due_to = ''
      this.sort_by = 'created_at'
      this.sort_dir = 'desc'
      this.with = ''
      this.save()
    },

    /** Build query params for router.get */
    toQuery() {
      const q = {
        status: this.status || undefined,
        priority: this.priority || undefined,
        category_id: this.category_id || undefined,
        search: this.search || undefined,
        due_from: this.due_from || undefined,
        due_to: this.due_to || undefined,
        sort_by: this.sort_by || undefined,
        sort_dir: this.sort_dir || undefined,
        with: this.with || undefined,
      }
      return q
    },

    /** Optional: sync initial props from server into the store */
    setFromInertia({ filters = {}, sorting = {} }) {
      this.status = filters.status ?? this.status
      this.priority = filters.priority ?? this.priority
      this.category_id = filters.category_id ?? this.category_id
      this.search = filters.search ?? this.search
      this.due_from = filters.due_from ?? this.due_from
      this.due_to = filters.due_to ?? this.due_to
      this.with = filters.with ?? this.with
      this.sort_by = sorting.sort_by ?? this.sort_by
      this.sort_dir = sorting.sort_dir ?? this.sort_dir
      this.save()
    },
  },
})
