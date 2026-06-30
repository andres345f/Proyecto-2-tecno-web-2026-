<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Entrega {
    id: number;
    ruta_archivo: string | null;
    fecha_entrega: string;
    nota: string | null;
    retroalimentacion: string | null;
    tarea: {
        id: number;
        titulo: string;
        puntaje_maximo: string;
        grupo: {
            id: number;
            codigo: string;
            materia: { nombre: string; codigo: string };
        };
    };
    usuario: { id: number; name: string; email: string };
}

const props = defineProps<{
    entrega: Entrega;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Entrega', href: `/entregas/${props.entrega.id}` },
];

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const downloadFile = () => {
    window.location.href = `/entregas/${props.entrega.id}/download`;
};
</script>

<template>
    <Head :title="`Entrega — ${entrega.tarea.titulo}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalle de Entrega</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ entrega.tarea.grupo.codigo }} — {{ entrega.tarea.titulo }}</p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Estudiante</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ entrega.usuario?.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Entrega</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(entrega.fecha_entrega) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nota</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            <template v-if="entrega.nota !== null"> {{ entrega.nota }} / {{ entrega.tarea.puntaje_maximo }} </template>
                            <template v-else>
                                <span class="text-yellow-600 dark:text-yellow-400">Sin calificar</span>
                            </template>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Archivo</p>
                        <button
                            v-if="entrega.ruta_archivo"
                            @click="downloadFile"
                            class="mt-1 text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400"
                        >
                            Descargar archivo
                        </button>
                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                    </div>
                </div>

                <div v-if="entrega.retroalimentacion" class="mt-6">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Retroalimentación</p>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ entrega.retroalimentacion }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
