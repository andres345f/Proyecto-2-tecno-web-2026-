<script setup lang="ts">
import ModalPagoQr from '@/Components/Pagos/ModalPagoQr.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface Cuota {
    id: number;
    descripcion: string;
    monto: string;
    fecha_vencimiento: string;
    estado: string;
    matricula_periodo: {
        periodo_academico: {
            nombre: string;
        };
    };
    pago: {
        id: number;
        estado: string;
    } | null;
}

const props = defineProps<{
    cuotas: Cuota[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pagos', href: route('pagos.index') },
]);

const showModal = ref(false);
const qrImage = ref<string | null>(null);
const transaccionId = ref<string | null>(null);
const isGenerating = ref(false);
const generandoCuotaId = ref<number | null>(null);

async function generarQr(cuotaId: number) {
    isGenerating.value = true;
    generandoCuotaId.value = cuotaId;

    try {
        const response = await fetch('/api/pagos/generar-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find((c) => c.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] ?? '',
                ),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ cuota_id: cuotaId }),
        });

        const data = await response.json();

        if (response.ok) {
            qrImage.value = data.qr_image;
            transaccionId.value = data.transaccion_id;
            showModal.value = true;
        }
    } catch {
        // Handle error silently
    } finally {
        isGenerating.value = false;
        generandoCuotaId.value = null;
    }
}

function handlePaymentCompleted() {
    // Reload the page to reflect updated status
    window.location.reload();
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function formatCurrency(amount: string) {
    return `$${parseFloat(amount).toFixed(2)}`;
}

function getEstadoBadgeClass(estado: string) {
    switch (estado) {
        case 'pagado':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'pendiente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'vencido':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-secondary text-secondary-foreground';
    }
}
</script>

<template>
    <Head title="Mis Pagos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Mis Pagos</h1>
                    <p class="text-sm text-muted-foreground mt-1">Estado de cuenta y cuotas pendientes.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Historial de Cuotas</CardTitle>
                    <CardDescription>Visualiza tus cuotas de pago registradas en el sistema.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Descripción
                                    </th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Monto</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Vencimiento
                                    </th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                                    <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="cuota in cuotas" :key="cuota.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle text-foreground font-semibold">
                                        {{ cuota.descripcion }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ formatCurrency(cuota.monto) }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ formatDate(cuota.fecha_vencimiento) }}
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                                getEstadoBadgeClass(cuota.estado),
                                            ]"
                                        >
                                            {{ cuota.estado === 'pagado' ? 'Pagado' : cuota.estado === 'pendiente' ? 'Pendiente' : 'Vencido' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-right">
                                        <Button
                                            v-if="cuota.estado === 'pendiente'"
                                            :disabled="isGenerating && generandoCuotaId === cuota.id"
                                            size="sm"
                                            @click="generarQr(cuota.id)"
                                        >
                                            <span v-if="isGenerating && generandoCuotaId === cuota.id">Generando...</span>
                                            <span v-else>Generar QR</span>
                                        </Button>
                                        <span v-else-if="cuota.estado === 'pagado'" class="text-green-600 dark:text-green-400 font-medium"> ✓ Pagado </span>
                                    </td>
                                </tr>
                                <tr v-if="cuotas.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">No hay cuotas registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <ModalPagoQr
            :show="showModal"
            :qr-image="qrImage"
            :transaccion-id="transaccionId"
            @close="
                showModal = false;
                qrImage = null;
                transaccionId = null;
            "
            @completed="handlePaymentCompleted"
        />
    </AppLayout>
</template>
