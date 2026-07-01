<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface Tarea {
    id: number;
    titulo: string;
    descripcion: string | null;
    fecha_vencimiento: string;
    puntaje_maximo: string;
    entregas: Array<{
        id: number;
        nota: string | null;
        fecha_entrega: string;
    }>;
}

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
}

const props = defineProps<{
    grupo: Grupo;
    tareas: Tarea[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Grupos', href: route('grupos.index') },
    { title: props.grupo.codigo, href: route('grupos.show', props.grupo.id) },
    { title: 'Tareas', href: route('grupos.tareas.index', props.grupo.id) },
];

const isPastDeadline = (fecha: string) => new Date(fecha) < new Date();

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
</script>

<template>
    <Head title="Tareas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Tareas</h1>
                    <p class="text-sm text-muted-foreground mt-1">{{ grupo.codigo }} — {{ grupo.materia?.nombre }}</p>
                </div>
                <Button v-if="$page.props.auth.user?.is_profesor" as-child>
                    <Link :href="route('grupos.tareas.create', grupo.id)">Crear Tarea</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de Tareas</CardTitle>
                    <CardDescription>Visualiza las asignaciones, fechas de vencimiento y puntajes de este grupo.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Título</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Vencimiento</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Puntaje Máximo</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                                    <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="tarea in tareas" :key="tarea.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground">
                                        {{ tarea.titulo }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground font-mono">
                                        {{ formatDate(tarea.fecha_vencimiento) }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ tarea.puntaje_maximo }} pts
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                            :class="[
                                                isPastDeadline(tarea.fecha_vencimiento)
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'
                                                    : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                            ]"
                                        >
                                            {{ isPastDeadline(tarea.fecha_vencimiento) ? 'Cerrada' : 'Abierta' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-right">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('grupos.tareas.show', [grupo.id, tarea.id])">Ver Detalle</Link>
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="tareas.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay tareas registradas para este grupo.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
