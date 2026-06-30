<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
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
    plan: PlanPago;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Planes de Pago', href: '/planes-pago' },
    { title: props.plan.nombre, href: `/planes-pago/${props.plan.id}` },
];
</script>

<template>
    <Head :title="plan.nombre" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">{{ plan.nombre }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">Detalles y condiciones financieras del plan.</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/planes-pago/${plan.id}/edit`">Editar</Link>
                    </Button>
                    <Button variant="ghost" as-child>
                        <Link href="/planes-pago">Volver</Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Información del Plan</CardTitle>
                    <CardDescription>Resumen de cobros y financiamientos asociados.</CardDescription>
                </CardHeader>
                <CardContent>
                    <dl class="space-y-4">
                        <div class="border-b border-border pb-3">
                            <dt class="text-sm font-semibold text-muted-foreground">Oferta Académica</dt>
                            <dd class="text-sm text-foreground mt-1">
                                {{ plan.oferta_academica?.nombre }} ({{ plan.oferta_academica?.codigo }})
                            </dd>
                        </div>
                        <div class="border-b border-border pb-3">
                            <dt class="text-sm font-semibold text-muted-foreground">Tipo</dt>
                            <dd class="text-sm text-foreground mt-1">{{ plan.tipo }}</dd>
                        </div>
                        <div class="border-b border-border pb-3">
                            <dt class="text-sm font-semibold text-muted-foreground">Monto Matrícula</dt>
                            <dd class="text-sm text-foreground mt-1 font-mono">${{ plan.monto_matricula }}</dd>
                        </div>
                        <div class="border-b border-border pb-3">
                            <dt class="text-sm font-semibold text-muted-foreground">Monto Cuota</dt>
                            <dd class="text-sm text-foreground mt-1 font-mono">${{ plan.monto_cuota }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-muted-foreground">Cantidad Cuotas</dt>
                            <dd class="text-sm text-foreground mt-1">{{ plan.cantidad_cuotas }}</dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
