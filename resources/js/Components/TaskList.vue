<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-gray-800">Tasks</h2>
      <button
        @click="showNewTaskForm = true"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Add Task
      </button>
    </div>

    <!-- New Task Form -->
    <div v-if="showNewTaskForm" class="mb-6 p-4 bg-gray-50 rounded">
      <form @submit.prevent="createTask" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Title</label>
          <input
            v-model="newTask.title"
            type="text"
            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            v-model="newTask.description"
            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          ></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Due Date</label>
          <input
            v-model="newTask.due_date"
            type="datetime-local"
            class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="showNewTaskForm = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded shadow-sm hover:bg-blue-600"
          >
            Create Task
          </button>
        </div>
      </form>
    </div>

    <!-- Task List -->
    <div class="space-y-4">
      <div
        v-for="task in tasks"
        :key="task.id"
        class="flex items-center justify-between p-4 bg-gray-50 rounded"
      >
        <div class="flex items-center space-x-4">
          <input
            type="checkbox"
            :checked="task.completed"
            @change="toggleTask(task)"
            class="h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500"
          />
          <div>
            <h3 class="text-lg font-medium" :class="{ 'line-through': task.completed }">
              {{ task.title }}
            </h3>
            <p class="text-sm text-gray-500">{{ task.description }}</p>
            <p v-if="task.due_date" class="text-sm text-gray-500">
              Due: {{ formatDate(task.due_date) }}
            </p>
          </div>
        </div>
        <button
          @click="deleteTask(task)"
          class="text-red-500 hover:text-red-700"
        >
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const tasks = ref([])
const showNewTaskForm = ref(false)
const newTask = ref({
  title: '',
  description: '',
  due_date: ''
})

const fetchTasks = async () => {
  try {
    const response = await axios.get('/api/tasks')
    tasks.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching tasks:', error)
  }
}

const createTask = async () => {
  try {
    const response = await axios.post('/api/tasks', newTask.value)
    if (response.data) {
      await fetchTasks()
      showNewTaskForm.value = false
      newTask.value = { title: '', description: '', due_date: '' }
    }
  } catch (error) {
    console.error('Error creating task:', error)
  }
}

const toggleTask = async (task) => {
  if (!task || !task.id) return
  try {
    const response = await axios.post(`/api/tasks/${task.id}/complete`)
    if (response.data && response.data.status === 'success') {
      await fetchTasks()
    }
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

const deleteTask = async (task) => {
  if (!task || !task.id) return
  if (!confirm('Are you sure you want to delete this task?')) return

  try {
    await axios.delete(`/api/tasks/${task.id}`)
    await fetchTasks()
  } catch (error) {
    console.error('Error deleting task:', error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

onMounted(() => {
  fetchTasks()
})
</script> 