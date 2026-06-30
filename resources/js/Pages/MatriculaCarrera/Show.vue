<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface PeriodoAcademico {
    id: number;
    nombre: string;
    tipo: string;
}

interface PlanPago {
    id: number;
    nombre: string;
    monto_matricula: number;
    monto_cuota: number;
    cantidad_cuotas: number;
}

interface MatriculaPeriodo {
    id: number;
    fecha_matricula: string;
    estado: string;
    periodo_academico: PeriodoAcademico;
    plan_pago: PlanPago;
}

interface MatriculaCarrera {
    id: number;
    fecha_matricula: string;
    estado: string;
    usuario: { name: string; email: string };
    oferta_academica: { nombre: string; codigo: string };
    matriculas_periodo: MatriculaPeriodo[];
}

const props = defineProps<{
    matricula: MatriculaCarrera;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Matrículas de Carrera', href: '/matriculas-carrera' },
    { title: props.matricula.usuario?.name || 'Detalle', href: `/matriculas-carrera/${props.matricula.id}` },
];

const estadoBadge = (estado: string) => {
    switch (estado) {
        case 'activo':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'inactivo':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'retirado':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 'completado':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        default:
            return 'bg-secondary text-secondary-foreground';
    }
};

const updateEstado = (estado: string) => {
    router.put(`/matriculas-carrera/${props.matricula.id}`, { estado });
};
</script>

<template>
    <Head :title="`Matrícula - ${matricula.usuario?.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Matrícula de {{ matricula.usuario?.name }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">Detalle de matrícula y períodos inscritos del estudiante.</p>
                </div>
                <div class="flex gap-2">
                    <Button as-child>
                        <Link :href="`/matriculas-periodo/create?matricula_carrera_id=${matricula.id}`">
                            Inscribir Período
                        </Link>
                    </Button>
                    <Button variant="ghost" as-child>
                        <Link href="/matriculas-carrera">Volver</Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info Card -->
                <Card class="md:col-span-1 h-fit">
                    <CardHeader>
                        <CardTitle>Información General</CardTitle>
                        <CardDescription>Resumen de la matrícula de carrera.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Estudiante</dt>
                                <dd class="text-sm text-foreground mt-1 font-medium">{{ matricula.usuario?.name }}</dd>
                                <dd class="text-xs text-muted-foreground">{{ matricula.usuario?.email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Carrera</dt>
                                <dd class="text-sm text-foreground mt-1 font-medium">
                                    {{ matricula.oferta_academica?.nombre }}
                                </dd>
                                <dd class="text-xs text-muted-foreground">Código: {{ matricula.oferta_academica?.codigo }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Fecha de Matrícula</dt>
                                <dd class="text-sm text-foreground mt-1">{{ matricula.fecha_matricula }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Estado</dt>
                                <dd class="text-sm mt-1">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider', estadoBadge(matricula.estado)]">
                                        {{ matricula.estado }}
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 flex flex-col gap-2 border-t border-border pt-4">
                            <Button
                                v-if="matricula.estado !== 'activo'"
                                @click="updateEstado('activo')"
                                class="w-full bg-green-600 hover:bg-green-500 text-white"
                            >
                                Activar
                            </Button>
                            <Button
                                v-if="matricula.estado !== 'inactivo'"
                                @click="updateEstado('inactivo')"
                                variant="outline"
                                class="w-full border-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-950/20 text-yellow-600"
                            >
                                Desactivar
                            </Button>
                            <Button
                                v-if="matricula.estado !== 'retirado'"
                                @click="updateEstado('retirado')"
                                variant="destructive"
                                class="w-full"
                            >
                                Retirar
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Periodos Card -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Períodos Inscritos</CardTitle>
                        <CardDescription>Historial de inscripciones a períodos académicos asociados a esta carrera.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div v-if="matricula.matriculas_periodo && matricula.matriculas_periodo.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Período</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Plan</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                                        <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="mp in matricula.matriculas_periodo" :key="mp.id" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 align-middle font-semibold text-foreground">
                                            {{ mp.periodo_academico?.nombre }}
                                        </td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground">{{ mp.plan_pago?.nombre }}</td>
                                        <td class="px-6 py-4 align-middle">
                                            <span
                                                :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider', estadoBadge(mp.estado)]"
                                            >
                                                {{ mp.estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-right">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/matriculas-periodo/${mp.id}`">
                                                    Ver Cuotas
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="p-6 text-center text-muted-foreground">
                            No hay períodos inscritos.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
