<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface MateriaPrerequisito {
    id: number;
    nombre: string;
    codigo: string;
}

interface Materia {
    id: number;
    nombre: string;
    codigo: string;
    descripcion: string | null;
    prerrequisitos: MateriaPrerequisito[];
    es_prerequisito_de: MateriaPrerequisito[];
}

const props = defineProps<{
    materia: Materia;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Materias', href: route('materias.index') },
    { title: props.materia.nombre, href: route('materias.show', props.materia.id) },
];
</script>

<template>
    <Head :title="materia.nombre" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ materia.nombre }}</h1>
                <div class="flex gap-2">
                    <Link
                        :href="route('materias.edit', props.materia.id)"
                        class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >
                        Editar
                    </Link>
                    <Link :href="route('materias.index')" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"> Volver </Link>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Información</h2>
                    <dl class="mt-4 space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ materia.codigo }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ materia.descripcion || 'Sin descripción' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Prerrequisitos</h2>
                    <ul v-if="materia.prerrequisitos.length > 0" class="mt-4 space-y-2">
                        <li v-for="prerequisito in materia.prerrequisitos" :key="prerequisito.id" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ prerequisito.codigo }} - {{ prerequisito.nombre }}
                        </li>
                    </ul>
                    <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">No tiene prerrequisitos.</p>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Es prerequisito de</h2>
                    <ul v-if="materia.es_prerequisito_de.length > 0" class="mt-4 space-y-2">
                        <li v-for="materiaHija in materia.es_prerequisito_de" :key="materiaHija.id" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ materiaHija.codigo }} - {{ materiaHija.nombre }}
                        </li>
                    </ul>
                    <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">No es prerequisito de ninguna materia.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
