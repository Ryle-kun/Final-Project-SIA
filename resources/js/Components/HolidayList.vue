<template>
  <div class="bg-white shadow rounded-lg p-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-2xl font-semibold text-gray-800">Holidays</h2>
      <div class="flex space-x-2">
        <button
          @click="getMonthHolidays"
          class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600"
          :class="{ 'bg-blue-700': view === 'month' }"
        >
          This Month
        </button>
        <button
          @click="getYearHolidays"
          class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600"
          :class="{ 'bg-blue-700': view === 'year' }"
        >
          This Year
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center text-gray-500 text-sm py-2">
      Loading holidays...
    </div>

    <div v-else-if="error" class="text-red-500 text-center text-sm py-2">
      {{ error }}
    </div>

    <div v-else-if="holidays.length > 0" class="space-y-2 max-h-[300px] overflow-y-auto">
      <div
        v-for="holiday in holidays"
        :key="holiday.name"
        class="p-2 bg-gray-50 rounded text-sm hover:bg-gray-100 transition-colors"
      >
        <div class="flex justify-between items-start">
          <div>
            <h3 class="font-medium">{{ holiday.name }}</h3>
            <p class="text-xs text-gray-500">{{ formatDate(holiday.date.iso) }}</p>
          </div>
          <button 
            @click="holiday.showDescription = !holiday.showDescription"
            class="text-gray-400 hover:text-gray-600 mt-1"
          >
            <span v-if="!holiday.showDescription">▼</span>
            <span v-else>▲</span>
          </button>
        </div>
        <p 
          v-if="holiday.showDescription" 
          class="text-xs text-gray-500 mt-1"
        >
          {{ holiday.description }}
        </p>
      </div>
    </div>

    <div v-else class="text-center text-gray-500 text-sm py-2">
      Click a button above to view holidays
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const holidays = ref([])
const loading = ref(false)
const error = ref('')
const view = ref('')

const getMonthHolidays = async () => {
  if (view.value === 'month') {
    view.value = ''
    holidays.value = []
    return
  }

  loading.value = true
  view.value = 'month'
  try {
    error.value = ''
    const response = await axios.get('/api/holidays/month')
    const rawHolidays = response.data.response.holidays || []
    
    // Filter out duplicates based on name and date
    const uniqueHolidays = rawHolidays.reduce((acc, holiday) => {
      const key = `${holiday.name}-${holiday.date.iso}`
      if (!acc.has(key)) {
        acc.set(key, { ...holiday, showDescription: false })
      }
      return acc
    }, new Map())
    
    holidays.value = Array.from(uniqueHolidays.values())
  } catch (e) {
    error.value = 'Could not fetch holidays. Please try again.'
    holidays.value = []
  } finally {
    loading.value = false
  }
}

const getYearHolidays = async () => {
  if (view.value === 'year') {
    view.value = ''
    holidays.value = []
    return
  }

  loading.value = true
  view.value = 'year'
  try {
    error.value = ''
    const response = await axios.get('/api/holidays/year')
    const rawHolidays = response.data.response.holidays || []
    
    // Filter out duplicates based on name and date
    const uniqueHolidays = rawHolidays.reduce((acc, holiday) => {
      const key = `${holiday.name}-${holiday.date.iso}`
      if (!acc.has(key)) {
        acc.set(key, { ...holiday, showDescription: false })
      }
      return acc
    }, new Map())
    
    holidays.value = Array.from(uniqueHolidays.values())
  } catch (e) {
    error.value = 'Could not fetch holidays. Please try again.'
    holidays.value = []
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
.max-h-[300px] {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E0 #EDF2F7;
}

.max-h-[300px]::-webkit-scrollbar {
  width: 6px;
}

.max-h-[300px]::-webkit-scrollbar-track {
  background: #EDF2F7;
  border-radius: 3px;
}

.max-h-[300px]::-webkit-scrollbar-thumb {
  background-color: #CBD5E0;
  border-radius: 3px;
}
</style> 