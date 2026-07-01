<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
}

interface PlanPago {
    id: number;
    nombre: string;
    tipo: string;
    monto_matricula: number;
    monto_cuota: number;
    cantidad_cuotas: number;
    oferta_academica: OfertaAcademica;
}

const props = defineProps<{
    planes: PlanPago[];
    ofertas: OfertaAcademica[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Planes de Pago', href: route('planes-pago.index') },
];

const confirmDelete = (plan: PlanPago) => {
    if (confirm(`¿Eliminar plan "${plan.nombre}"?`)) {
        router.delete(route('planes-pago.destroy', plan.id));
    }
};
</script>

<template>
    <Head title="Planes de Pago" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Planes de Pago</h1>
                    <p class="text-sm text-muted-foreground mt-1">Configuración y administración de tarifas, matrículas y planes de cuotas.</p>
                </div>
                <Button as-child>
                    <Link :href="route('planes-pago.create')">Crear Plan</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de Planes</CardTitle>
                    <CardDescription>Planes vigentes asociados a las distintas ofertas académicas.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Nombre</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Tipo</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Oferta</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Matrícula</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Cuota</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">N° Cuotas</th>
                                    <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="plan in planes" :key="plan.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle text-foreground font-semibold">{{ plan.nombre }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ plan.tipo }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ plan.oferta_academica?.nombre }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">${{ plan.monto_matricula }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">${{ plan.monto_cuota }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ plan.cantidad_cuotas }}</td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('planes-pago.show', plan.id)">Ver</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('planes-pago.edit', plan.id)">Editar</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmDelete(plan)">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="planes.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay planes de pago registrados.
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
