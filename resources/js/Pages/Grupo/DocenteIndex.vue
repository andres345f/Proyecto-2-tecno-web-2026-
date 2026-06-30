<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { computed } from 'vue';
import WeeklySchedule from '@/components/WeeklySchedule.vue';
import { Calendar } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

interface Horario {
    dia: string;
    hora_inicio: string;
    hora_fin: string;
    aula: { nombre: string };
}

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
    periodo_academico: { nombre: string };
    horarios: Horario[];
}

const props = defineProps<{
    grupos: Grupo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Mis Grupos', href: '/grupos-docente' },
];

const formatHorarios = (horarios: Horario[]) => {
    if (!horarios || horarios.length === 0) return 'Sin horarios asignados';
    return horarios.map((h) => `${h.dia} ${h.hora_inicio.slice(0, 5)}-${h.hora_fin.slice(0, 5)} (${h.aula?.nombre})`).join(', ');
};

// Map all classes schedules for the weekly timetable
const allHorarios = computed(() => {
    const list: any[] = [];
    props.grupos.forEach(grupo => {
        if (grupo.horarios) {
            grupo.horarios.forEach(h => {
                list.push({
                    dia: h.dia,
                    hora_inicio: h.hora_inicio,
                    hora_fin: h.hora_fin,
                    grupo_codigo: grupo.codigo,
                    materia_nombre: grupo.materia?.nombre,
                    aula_nombre: h.aula?.nombre
                });
            });
        }
    });
    return list;
});
</script>

<template>
    <Head title="Mis Grupos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Mis Grupos</h1>
                    <p class="text-sm text-muted-foreground mt-1">Listado de los grupos académicos a los que tienes asignados como docente.</p>
                </div>
                
                <!-- Weekly schedule dialog trigger -->
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="outline" class="flex items-center gap-2">
                            <Calendar class="w-4 h-4" />
                            Ver Horario Semanal
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-5xl">
                        <DialogHeader>
                            <DialogTitle>Mi Horario Semanal</DialogTitle>
                            <DialogDescription>
                                Distribución de tus clases para este período académico.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="mt-4">
                            <WeeklySchedule 
                                title="Distribución de Clases" 
                                :horarios="allHorarios" 
                                type="usuario"
                            />
                        </div>
                    </DialogContent>
                </Dialog>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Grupos Asignados</CardTitle>
                    <CardDescription>Visualiza y administra tus materias y el avance de tus estudiantes.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Código</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Materia</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Horarios</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Período</th>
                                    <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="grupo in grupos" :key="grupo.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground font-mono">{{ grupo.codigo }}</td>
                                    <td class="px-6 py-4 align-middle text-foreground font-medium">
                                        <div class="flex flex-col">
                                            <span>{{ grupo.materia?.nombre }}</span>
                                            <span class="text-xs text-muted-foreground font-mono">{{ grupo.materia?.codigo }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ formatHorarios(grupo.horarios) }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ grupo.periodo_academico?.nombre }}</td>
                                    <td class="px-6 py-4 align-middle text-right">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/grupos-docente/${grupo.id}`">Ver Clases y Alumnos</Link>
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="grupos.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                        No tienes grupos asignados en este período.
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
