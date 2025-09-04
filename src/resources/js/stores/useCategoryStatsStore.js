import { defineStore } from 'pinia'
import axios from 'axios'

export const useCategoryStatsStore = defineStore('categoryStats', {
  state: () => ({
    stats: [],     // array of rows
    totals: null,  // { total, pending, in_progress, completed }
    loading: false,
    lastUpdated: null,
  }),

  actions: {
    setFromInertia({ stats, totals }) {
      if (Array.isArray(stats)) this.stats = stats
      if (totals) this.totals = totals
      this.lastUpdated = new Date().toISOString()
    },

    async fetch(force = false) {
      try {
        this.loading = true
        // Controller must return JSON when requested (see step 4)
        const { data } = await axios.get('/categories/statistics', {
          headers: { 'Accept': 'application/json' }
        })
        // expecting { stats: [...], totals: {...} }
        if (Array.isArray(data?.stats)) this.stats = data.stats
        if (data?.totals) this.totals = data.totals
        this.lastUpdated = new Date().toISOString()
      } finally {
        this.loading = false
      }
    }
  }
})
