<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Timezone Lookup</h2>

    <div class="mb-4">
      <div class="flex space-x-2">
        <input
          v-model="city"
          type="text"
          placeholder="Enter city or country name"
          class="flex-1 rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          @keyup.enter="getTimezone"
        />
        <button
          @click="getTimezone"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
          Search
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center text-gray-500">
      Loading timezone information...
    </div>

    <div v-else-if="error" class="text-red-500 text-center">
      {{ error }}
    </div>

    <div v-else-if="timezoneData" class="space-y-4">
      <div class="text-center">
        <h3 class="text-xl font-medium">{{ timezoneData.timezone || 'Unknown Timezone' }}</h3>
        <div class="text-4xl font-bold">{{ formatTime(timezoneData.current_time) }}</div>
      </div>

      <div class="grid grid-cols-2 gap-4 text-center">
        <div class="bg-gray-50 p-3 rounded">
          <div class="text-sm text-gray-500">Location</div>
          <div class="font-medium">
            {{ timezoneData.country || 'Unknown' }}
            <span v-if="timezoneData.region">, {{ timezoneData.region }}</span>
          </div>
        </div>
        <div class="bg-gray-50 p-3 rounded">
          <div class="text-sm text-gray-500">GMT Offset</div>
          <div class="font-medium">{{ formatGMTOffset(timezoneData.gmt_offset) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const city = ref('')
const timezoneData = ref(null)
const loading = ref(false)
const error = ref('')

const formatTime = (time) => {
  if (!time) return 'N/A'
  try {
    const date = new Date(time)
    if (isNaN(date.getTime())) {
      // If the date is invalid, try parsing it as a Unix timestamp
      const timestamp = parseInt(time)
      if (!isNaN(timestamp)) {
        return new Date(timestamp * 1000).toLocaleString()
      }
      return time // Return the original string if parsing fails
    }
    return date.toLocaleString()
  } catch (e) {
    console.error('Time formatting error:', e)
    return time // Return the original string if formatting fails
  }
}

const formatGMTOffset = (offset) => {
  if (offset === undefined || offset === null) return 'GMT+00:00'
  try {
    const hours = Math.floor(Math.abs(offset) / 3600)
    const minutes = Math.floor((Math.abs(offset) % 3600) / 60)
    const sign = offset >= 0 ? '+' : '-'
    return `GMT${sign}${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`
  } catch (e) {
    console.error('GMT offset formatting error:', e)
    return 'GMT+00:00'
  }
}

const getTimezone = async () => {
  if (!city.value.trim()) {
    error.value = 'Please enter a city or country name'
    return
  }

  loading.value = true
  error.value = ''
  timezoneData.value = null

  try {
    const response = await axios.get('/api/timezone/city', {
      params: { city: city.value.trim() }
    })
    
    console.log('Timezone API response:', response.data)
    
    if (!response.data.success) {
      throw new Error(response.data.error || 'Failed to fetch timezone data')
    }
    
    timezoneData.value = response.data.data

  } catch (e) {
    console.error('Timezone API error:', e)
    error.value = e.message || 'Could not fetch timezone data. Please try again.'
    timezoneData.value = null
  } finally {
    loading.value = false
  }
}
</script> 