<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface Horario {
    id: number;
    dia: string;
    hora_inicio: string;
    hora_fin: string;
    aula: { nombre: string; codigo: string };
}

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
    periodo_academico: { nombre: string };
    docente: { name: string };
    horarios: Horario[];
}

interface Matricula {
    id: number;
    estado: string;
    matricula_periodo: {
        matricula_carrera: {
            usuario: { name: string; email: string };
        };
    };
}

const props = defineProps<{
    grupo: Grupo;
    matriculas: Matricula[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Mis Grupos', href: route('grupos.docente.index') },
    { title: props.grupo.codigo, href: route('grupos.docente.show', props.grupo.id) },
];

const estadoBadge = (estado: string) => {
    const colors: Record<string, string> = {
        inscrito: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        en_curso: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        aprobado: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        reprobado: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        retirado: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return colors[estado] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Grupo ${grupo.codigo}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Grupo {{ grupo.codigo }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">Detalles de la materia, horarios y lista de alumnos.</p>
                </div>
                <div class="flex gap-3">
                    <Button as-child>
                        <Link :href="route('grupos.tareas.index', grupo.id)">Gestionar Tareas</Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="route('grupos.docente.index')">Volver</Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Información General</CardTitle>
                        <CardDescription>Detalles del curso y horarios establecidos.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Materia</span>
                                <span class="text-base font-semibold text-foreground mt-1 block">
                                    {{ grupo.materia?.nombre }} ({{ grupo.materia?.codigo }})
                                </span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Periodo Académico</span>
                                <span class="text-base font-medium text-foreground mt-1 block">
                                    {{ grupo.periodo_academico?.nombre }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-border pt-4">
                            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block mb-2">Horarios</span>
                            <div v-if="grupo.horarios && grupo.horarios.length > 0" class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/50 border-b border-border">
                                        <tr>
                                            <th class="h-10 px-4 text-left align-middle font-semibold text-muted-foreground">Día</th>
                                            <th class="h-10 px-4 text-left align-middle font-semibold text-muted-foreground">Hora Inicio</th>
                                            <th class="h-10 px-4 text-left align-middle font-semibold text-muted-foreground">Hora Fin</th>
                                            <th class="h-10 px-4 text-left align-middle font-semibold text-muted-foreground">Aula</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr v-for="horario in grupo.horarios" :key="horario.id" class="hover:bg-muted/10 transition-colors">
                                            <td class="px-4 py-3 align-middle font-medium text-foreground">{{ horario.dia }}</td>
                                            <td class="px-4 py-3 align-middle text-muted-foreground">{{ horario.hora_inicio }}</td>
                                            <td class="px-4 py-3 align-middle text-muted-foreground">{{ horario.hora_fin }}</td>
                                            <td class="px-4 py-3 align-middle text-muted-foreground">
                                                {{ horario.aula?.nombre }} ({{ horario.aula?.codigo }})
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="text-sm text-muted-foreground italic">No hay horarios registrados.</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Estadísticas</CardTitle>
                        <CardDescription>Resumen rápido de los estudiantes.</CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-col items-center justify-center p-6 space-y-2">
                        <span class="text-5xl font-extrabold text-foreground">{{ matriculas.length }}</span>
                        <span class="text-sm text-muted-foreground font-medium">Estudiantes Inscritos</span>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Lista de Estudiantes</CardTitle>
                    <CardDescription>Estudiantes actualmente inscritos en el grupo.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Nombre</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Email</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="matricula in matriculas" :key="matricula.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-medium text-foreground">
                                        {{ matricula.matricula_periodo?.matricula_carrera?.usuario?.name }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ matricula.matricula_periodo?.matricula_carrera?.usuario?.email }}
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                estadoBadge(matricula.estado),
                                            ]"
                                        >
                                            {{ matricula.estado }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="matriculas.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay estudiantes inscritos en este grupo.
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
