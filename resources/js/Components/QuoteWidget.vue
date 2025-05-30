<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Daily Quote</h2>

    <div v-if="loading" class="text-center text-gray-500">
      Loading quote...
    </div>

    <div v-else-if="error" class="text-red-500 text-center">
      {{ error }}
    </div>

    <div v-else-if="quote" class="space-y-4">
      <div class="text-center">
        <p class="text-xl italic">
          "{{ quote.body }}"
        </p>
        <p class="text-gray-500 mt-2">
          — {{ quote.author }}
        </p>
      </div>

      <div class="flex justify-center mt-4">
        <button
          @click="getRandomQuote"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
          New Quote
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const quote = ref(null)
const loading = ref(true)
const error = ref(null)

const getRandomQuote = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await axios.get('/api/quotes/random')
    if (response.data && response.data.success && response.data.data) {
      quote.value = response.data.data
    } else {
      error.value = response.data?.error || 'Failed to fetch quote'
    }
  } catch (e) {
    error.value = e.response?.data?.error || e.message || 'Failed to fetch quote'
  } finally {
    loading.value = false
  }
}

// Fetch quote when component mounts
getRandomQuote()
</script>

<style scoped>
.text-gray-500 {
  color: #6b7280;
}
</style>