<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

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
    oferta_academica_id: number;
}

const props = defineProps<{
    plan: PlanPago;
    ofertas: OfertaAcademica[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Planes de Pago', href: '/planes-pago' },
    { title: 'Editar', href: `/planes-pago/${props.plan.id}/edit` },
];

const form = useForm({
    oferta_academica_id: props.plan.oferta_academica_id,
    nombre: props.plan.nombre,
    tipo: props.plan.tipo,
    monto_matricula: props.plan.monto_matricula,
    monto_cuota: props.plan.monto_cuota,
    cantidad_cuotas: props.plan.cantidad_cuotas,
});

const submit = () => {
    form.put(`/planes-pago/${props.plan.id}`);
};
</script>

<template>
    <Head title="Editar Plan de Pago" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Editar Plan de Pago</h1>
                    <p class="text-sm text-muted-foreground mt-1">Modifica las condiciones del plan de pago existente.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/planes-pago">Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles del Plan de Pago</CardTitle>
                    <CardDescription>Especifica la oferta académica asociada y las condiciones de cobro.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="oferta_academica_id">Oferta Académica</Label>
                            <select
                                id="oferta_academica_id"
                                v-model="form.oferta_academica_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option v-for="oferta in ofertas" :key="oferta.id" :value="oferta.id">
                                    {{ oferta.nombre }} ({{ oferta.codigo }})
                                </option>
                            </select>
                            <InputError :message="form.errors.oferta_academica_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="nombre">Nombre</Label>
                            <Input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                placeholder="ej. Semestral Regular"
                                required
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <div class="space-y-2">
                            <Label for="tipo">Tipo</Label>
                            <select
                                id="tipo"
                                v-model="form.tipo"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="unico">Único</option>
                                <option value="por_periodo">Por Período</option>
                                <option value="especial">Especial</option>
                            </select>
                            <InputError :message="form.errors.tipo" />
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="monto_matricula">Monto Matrícula</Label>
                                <Input
                                    id="monto_matricula"
                                    v-model="form.monto_matricula"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                                <InputError :message="form.errors.monto_matricula" />
                            </div>

                            <div class="space-y-2">
                                <Label for="monto_cuota">Monto Cuota</Label>
                                <Input
                                    id="monto_cuota"
                                    v-model="form.monto_cuota"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                                <InputError :message="form.errors.monto_cuota" />
                            </div>

                            <div class="space-y-2">
                                <Label for="cantidad_cuotas">Cantidad Cuotas</Label>
                                <Input
                                    id="cantidad_cuotas"
                                    v-model="form.cantidad_cuotas"
                                    type="number"
                                    min="1"
                                    required
                                />
                                <InputError :message="form.errors.cantidad_cuotas" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <Button type="submit" :disabled="form.processing">
                                Actualizar
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link href="/planes-pago">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
