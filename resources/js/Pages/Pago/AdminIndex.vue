<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

interface Pago {
    id: number;
    monto_pagado: string;
    metodo_pago: string;
    transaccion_id: string;
    fecha_pago: string;
    estado: string;
    cuota: {
        descripcion: string;
        monto: string;
        matricula_periodo: {
            periodo_academico: {
                nombre: string;
            };
            matricula_carrera: {
                usuario: {
                    name: string;
                    email: string;
                };
                oferta_academica: {
                    nombre: string;
                };
            };
        };
    };
}

const props = defineProps<{
    pagos: {
        data: Pago[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters?: { search?: string; metodo_pago?: string; estado?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pagos', href: route('pagos.index') },
];

const search = ref(props.filters?.search || '');
const metodoPago = ref(props.filters?.metodo_pago || '');
const estado = ref(props.filters?.estado || '');

let timeout: any = null;
watch([search, metodoPago, estado], ([newSearch, newMetodo, newEstado]) => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('pagos.index'), {
            search: newSearch,
            metodo_pago: newMetodo,
            estado: newEstado
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

function formatDate(dateString: string) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatCurrency(amount: string) {
    return `$${parseFloat(amount).toFixed(2)}`;
}

function getEstadoBadgeClass(estado: string) {
    switch (estado) {
        case 'completado':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'pendiente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'fallido':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-secondary text-secondary-foreground';
    }
}
</script>

<template>
    <Head title="Registros de Pagos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Registros de Pagos</h1>
                    <p class="text-sm text-muted-foreground mt-1">Historial completo de transacciones de la plataforma.</p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('planes-pago.index')">Gestionar Planes de Pago</Link>
                </Button>
            </div>

            <Card>
                <CardHeader class="flex flex-col gap-4 border-b border-border pb-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <CardTitle>Historial de Transacciones</CardTitle>
                            <CardDescription>Consulta y audita todos los pagos realizados por los estudiantes.</CardDescription>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Input v-model="search" placeholder="Buscar estudiante o transacción..." />
                        </div>
                        <div>
                            <select v-model="estado" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">Todos los Estados</option>
                                <option value="completado">Completado</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="fallido">Fallido</option>
                            </select>
                        </div>
                        <div>
                            <select v-model="metodoPago" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">Todos los Métodos</option>
                                <option value="qr_pagofacil">PagoFácil QR</option>
                                <option value="efectivo">Efectivo</option>
                            </select>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estudiante</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Oferta Académica</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Concepto</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Monto</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Método</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Transacción ID</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Fecha de Pago</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="pago in pagos.data" :key="pago.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle">
                                        <div class="font-semibold text-foreground">
                                            {{ pago.cuota?.matricula_periodo?.matricula_carrera?.usuario?.name || 'Usuario desconocido' }}
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ pago.cuota?.matricula_periodo?.matricula_carrera?.usuario?.email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <div class="text-foreground">
                                            {{ pago.cuota?.matricula_periodo?.matricula_carrera?.oferta_academica?.nombre }}
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ pago.cuota?.matricula_periodo?.periodo_academico?.nombre }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ pago.cuota?.descripcion }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-foreground font-semibold">
                                        {{ formatCurrency(pago.monto_pagado) }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ pago.metodo_pago === 'qr_pagofacil' ? 'PagoFácil QR' : pago.metodo_pago }}
                                    </td>
                                    <td class="px-6 py-4 align-middle font-mono text-xs text-muted-foreground">
                                        {{ pago.transaccion_id }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ formatDate(pago.fecha_pago) }}
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider',
                                                getEstadoBadgeClass(pago.estado),
                                            ]"
                                        >
                                            {{ pago.estado === 'completado' ? 'Completado' : pago.estado }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="pagos.data.length === 0">
                                    <td colspan="8" class="px-6 py-8 text-center text-muted-foreground">
                                        No se encontraron registros de pagos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="pagos.links && pagos.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                :href="pagos.prev_page_url || '#'"
                                class="relative inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !pagos.prev_page_url }"
                            >
                                Anterior
                            </Link>
                            <Link
                                :href="pagos.next_page_url || '#'"
                                class="relative ml-3 inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !pagos.next_page_url }"
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Mostrando del
                                    <span class="font-medium text-foreground">{{ pagos.from || 0 }}</span>
                                    al
                                    <span class="font-medium text-foreground">{{ pagos.to || 0 }}</span>
                                    de
                                    <span class="font-medium text-foreground">{{ pagos.total || 0 }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in pagos.links"
                                        :key="i"
                                        :href="link.url || '#'"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-semibold focus:z-20"
                                        :class="[
                                            link.active
                                                ? 'z-10 bg-primary text-primary-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary'
                                                : 'text-foreground ring-1 ring-inset ring-border hover:bg-muted/50 focus:outline-offset-0',
                                            !link.url ? 'opacity-50 pointer-events-none' : '',
                                            i === 0 ? 'rounded-l-md' : '',
                                            i === pagos.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
