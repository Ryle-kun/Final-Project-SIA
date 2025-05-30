<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TaskList from '@/Components/TaskList.vue';
import WeatherWidget from '@/Components/WeatherWidget.vue';
import TimezoneWidget from '@/Components/TimezoneWidget.vue';
import HolidayList from '@/Components/HolidayList.vue';
import QuoteWidget from '@/Components/QuoteWidget.vue';
</script>

<template>
    <Head title="Dashboard - PlanIt" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Tasks Section -->
                    <div class="col-span-1 md:col-span-2">
                        <TaskList />
                    </div>

                    <!-- Weather Widget -->
                    <div class="transition hover:scale-[1.02]">
                        <WeatherWidget />
                    </div>

                    <!-- Timezone Widget -->
                    <div class="transition hover:scale-[1.02]">
                        <TimezoneWidget />
                    </div>

                    <!-- Holiday List -->
                    <div class="transition hover:scale-[1.02]">
                        <HolidayList />
                    </div>

                    <!-- Quote Widget -->
                    <div class="transition hover:scale-[1.02]">
                        <QuoteWidget />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
