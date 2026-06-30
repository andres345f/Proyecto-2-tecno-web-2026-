<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Feature {
    title: string;
    description: string;
    icon: string;
}

interface Props {
    institutionName: string;
    tagline: string;
    features: Feature[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head :title="institutionName" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Hero Section -->
        <div class="mx-auto max-w-7xl px-4 pb-16 pt-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl">
                    {{ institutionName }}
                </h1>
                <p class="mx-auto mt-3 max-w-md text-base text-gray-500 sm:text-lg md:mt-5 md:max-w-3xl md:text-xl">
                    {{ tagline }}
                </p>
                <div class="mt-10 flex justify-center gap-4">
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Iniciar Sesión
                    </Link>
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Conocer Más
                    </Link>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base font-semibold uppercase tracking-wide text-indigo-600">Características</h2>
                    <p class="mt-2 text-3xl font-extrabold leading-8 tracking-tight text-gray-900 sm:text-4xl">
                        Todo lo que necesitas para gestionar tu institución
                    </p>
                </div>

                <div class="mt-10">
                    <div class="space-y-10 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10 md:space-y-0">
                        <div v-for="feature in features" :key="feature.title" class="relative">
                            <div class="absolute flex h-12 w-12 items-center justify-center rounded-md bg-indigo-500 text-white">
                                <!-- Icon placeholder -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                    />
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ feature.title }}</h3>
                                <p class="mt-2 text-base text-gray-500">{{ feature.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-500">
                    <p>&copy; 2026 {{ institutionName }}. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </div>
</template>
