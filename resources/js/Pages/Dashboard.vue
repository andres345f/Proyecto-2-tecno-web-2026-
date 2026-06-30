<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
    is_propietario: boolean;
    is_director: boolean;
    is_secretaria: boolean;
    is_profesor: boolean;
    is_estudiante: boolean;
    is_activo: boolean;
}

const props = defineProps<{
    user: User;
    primaryRole: string;
    roles: string[];
}>();

const getRoleDisplayName = (role: string): string => {
    const roleNames: Record<string, string> = {
        propietario: 'Propietario',
        director: 'Director',
        secretaria: 'Secretaria',
        profesor: 'Profesor',
        estudiante: 'Estudiante',
    };
    return roleNames[role] || role;
};

const getRoleBadgeColor = (role: string): string => {
    const colors: Record<string, string> = {
        propietario: 'bg-purple-100 text-purple-800',
        director: 'bg-blue-100 text-blue-800',
        secretaria: 'bg-green-100 text-green-800',
        profesor: 'bg-yellow-100 text-yellow-800',
        estudiante: 'bg-gray-100 text-gray-800',
    };
    return colors[role] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Welcome Section -->
            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bienvenido, {{ user.name }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Panel de control - {{ getRoleDisplayName(primaryRole) }}</p>

                <!-- Role Badges -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <span
                        v-for="role in roles"
                        :key="role"
                        :class="['inline-flex items-center rounded-full px-3 py-1 text-sm font-medium', getRoleBadgeColor(role)]"
                    >
                        {{ getRoleDisplayName(role) }}
                    </span>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-lg font-semibold text-gray-700">Selecciona una opción del menú lateral</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
