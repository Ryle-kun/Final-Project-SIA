<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Weather</h2>
    
    <div class="mb-4">
      <div class="flex space-x-2">
        <input
          v-model="city"
          type="text"
          placeholder="Enter city name"
          class="flex-1 rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          @keyup.enter="getWeather"
        />
        <button
          @click="getWeather"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
          Search
        </button>
      </div>
    </div>

    <div v-if="weather" class="space-y-4">
      <div class="text-center">
        <h3 class="text-xl font-medium">{{ weather.name }}</h3>
        <div class="text-4xl font-bold">{{ Math.round(weather.main.temp) }}°C</div>
        <div class="text-gray-500">{{ weather.weather[0].description }}</div>
      </div>

      <div class="grid grid-cols-2 gap-4 text-center">
        <div class="bg-gray-50 p-3 rounded">
          <div class="text-sm text-gray-500">Humidity</div>
          <div class="font-medium">{{ weather.main.humidity }}%</div>
        </div>
        <div class="bg-gray-50 p-3 rounded">
          <div class="text-sm text-gray-500">Wind Speed</div>
          <div class="font-medium">{{ weather.wind.speed }} m/s</div>
        </div>
      </div>
    </div>

    <div v-if="error" class="text-red-500 text-center mt-4">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const city = ref('')
const weather = ref(null)
const error = ref('')

const getWeather = async () => {
  if (!city.value) return

  try {
    error.value = ''
    const response = await axios.get('/api/weather/current', {
      params: { city: city.value }
    })
    weather.value = response.data
  } catch (e) {
    error.value = 'Could not fetch weather data. Please try again.'
    weather.value = null
  }
}
</script> 