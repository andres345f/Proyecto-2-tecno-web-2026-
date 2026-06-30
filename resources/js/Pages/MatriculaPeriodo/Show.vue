<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { computed } from 'vue';

interface Cuota {
    id: number;
    descripcion: string;
    monto: number;
    fecha_vencimiento: string;
    estado: string;
}

interface MatriculaPeriodo {
    id: number;
    fecha_matricula: string;
    estado: string;
    periodo_academico: { nombre: string; tipo: string };
    plan_pago: { nombre: string; monto_matricula: number; monto_cuota: number; cantidad_cuotas: number };
    matricula_carrera: {
        id: number;
        usuario: { name: string; email: string };
        oferta_academica: { nombre: string; codigo: string };
    };
    cuotas: Cuota[];
}

const props = defineProps<{
    matricula: MatriculaPeriodo;
}>();

const page = usePage();
const isEstudiante = computed(() => !!(page.props.auth as any)?.user?.is_estudiante);

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (isEstudiante.value) {
        return [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Mis Grupos', href: '/mis-grupos' },
            { title: `Período - ${props.matricula.periodo_academico?.nombre}`, href: `/matriculas-periodo/${props.matricula.id}` },
        ];
    }
    return [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Matrículas de Carrera', href: '/matriculas-carrera' },
        { title: `Período - ${props.matricula.periodo_academico?.nombre}`, href: `/matriculas-periodo/${props.matricula.id}` },
    ];
});

const estadoBadge = (estado: string) => {
    switch (estado) {
        case 'activo':
        case 'pagado':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'inactivo':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        case 'completado':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'pendiente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'vencido':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-secondary text-secondary-foreground';
    }
};

const totalCuotas = props.matricula.cuotas?.reduce((sum, c) => sum + Number(c.monto), 0) || 0;
</script>

<template>

    <Head :title="`Período - ${matricula.periodo_academico?.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">
                        {{ matricula.periodo_academico?.nombre }}
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">Inscripción del estudiante {{
                        matricula.matricula_carrera?.usuario?.name }} en el período académico.</p>
                </div>
                <!-- <Button variant="outline" as-child>
                    <Link :href="isEstudiante ? '/mis-grupos' : `/matriculas-carrera/${matricula.matricula_carrera?.id}`">Volver</Link>
                </Button> -->
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Period Details -->
                <Card class="md:col-span-1 h-fit">
                    <CardHeader>
                        <CardTitle>Detalles del Período</CardTitle>
                        <CardDescription>Resumen de matrícula y planes asociados.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Carrera</dt>
                                <dd class="text-sm text-foreground mt-1 font-medium">
                                    {{ matricula.matricula_carrera?.oferta_academica?.nombre }}
                                </dd>
                                <dd class="text-xs text-muted-foreground">Código: {{
                                    matricula.matricula_carrera?.oferta_academica?.codigo }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Plan de Pago</dt>
                                <dd class="text-sm text-foreground mt-1 font-medium">{{ matricula.plan_pago?.nombre }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Fecha de Inscripción</dt>
                                <dd class="text-sm text-foreground mt-1">{{ matricula.fecha_matricula }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-muted-foreground">Estado</dt>
                                <dd class="text-sm mt-1">
                                    <span
                                        :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider', estadoBadge(matricula.estado)]">
                                        {{ matricula.estado }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <!-- Cuotas Generadas -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Cuotas Generadas</CardTitle>
                        <CardDescription>Obligaciones y financiamientos de matrícula e installments.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div v-if="matricula.cuotas && matricula.cuotas.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th
                                            class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                            Descripción</th>
                                        <th
                                            class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                            Monto</th>
                                        <th
                                            class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                            Vencimiento</th>
                                        <th
                                            class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                            Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="cuota in matricula.cuotas" :key="cuota.id"
                                        class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 align-middle font-medium text-foreground">{{
                                            cuota.descripcion }}</td>
                                        <td class="px-6 py-4 align-middle font-mono font-semibold text-foreground">
                                            ${{ Number(cuota.monto).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground">{{
                                            cuota.fecha_vencimiento }}</td>
                                        <td class="px-6 py-4 align-middle">
                                            <span :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider',
                                                estadoBadge(cuota.estado),
                                            ]">
                                                {{ cuota.estado }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-muted/30 border-t border-border">
                                    <tr>
                                        <td class="px-6 py-4 align-middle font-bold text-foreground">Total</td>
                                        <td class="px-6 py-4 align-middle font-mono font-bold text-foreground"
                                            colspan="3">
                                            ${{ totalCuotas.toFixed(2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <p v-else class="p-6 text-center text-muted-foreground">No hay cuotas generadas.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
