<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-indigo-950">
        <div class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
            <div class="mb-4">
                <Link href="/" class="text-gray-900">
                    <ApplicationLogo class="w-auto" />
                </Link>
            </div>

            <div class="w-full overflow-hidden bg-white dark:bg-gray-800 px-6 py-8 shadow-xl sm:max-w-md sm:rounded-xl">
                <slot />
            </div>
        </div>
    </div>
</template>
