<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface Periodo {
    id: number;
    nombre: string;
    tipo: string;
}

interface Docente {
    id: number;
    name: string;
}

interface GrupoPeriodo {
    id: number;
    cupo_maximo: number;
    periodo_academico: Periodo;
    docente?: Docente;
}

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
    grupo_periodos?: GrupoPeriodo[];
}

const props = defineProps<{
    grupo: Grupo;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Grupos', href: route('grupos.index') },
    { title: props.grupo.codigo, href: route('grupos.show', props.grupo.id) },
];
</script>

<template>
    <Head :title="`Grupo ${grupo.codigo}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Grupo {{ grupo.codigo }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">Información de catálogo e instancias académicas activas.</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('grupos.edit', grupo.id)">Editar Catálogo</Link>
                    </Button>
                    <Button variant="ghost" as-child>
                        <Link :href="route('grupos.index')">Volver al Catálogo</Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info Card -->
                <Card class="md:col-span-1 border border-muted h-fit">
                    <CardHeader class="pb-3">
                        <CardTitle>Detalles del Grupo</CardTitle>
                        <CardDescription>Datos del catálogo base.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Código de Catálogo</span>
                            <p class="text-lg font-bold text-foreground mt-0.5">{{ grupo.codigo }}</p>
                        </div>
                        <div class="pt-2 border-t">
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Materia / Asignatura</span>
                            <p class="text-base font-semibold text-foreground mt-0.5">{{ grupo.materia?.nombre }}</p>
                            <span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-0.5 rounded mt-1 inline-block">
                                {{ grupo.materia?.codigo }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Instancias Card -->
                <Card class="md:col-span-2 border border-muted">
                    <CardHeader>
                        <CardTitle>Instancias por Período Académico</CardTitle>
                        <CardDescription>Períodos académicos en los que se ofrece este grupo con su respectivo docente y cupos.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="grupo.grupo_periodos && grupo.grupo_periodos.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs uppercase bg-muted/65 text-muted-foreground">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Período Académico</th>
                                        <th class="px-4 py-3 font-semibold">Docente Asignado</th>
                                        <th class="px-4 py-3 font-semibold text-right">Cupo Máximo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="gp in grupo.grupo_periodos" :key="gp.id" class="hover:bg-accent/15 transition-colors">
                                        <td class="px-4 py-3.5 font-medium text-foreground">
                                            <Link :href="route('periodos-academicos.show', gp.periodo_academico?.id)" class="text-primary hover:underline font-semibold">
                                                {{ gp.periodo_academico?.nombre }}
                                            </Link>
                                            <span class="text-xs text-muted-foreground block capitalize">{{ gp.periodo_academico?.tipo }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-muted-foreground">
                                            {{ gp.docente?.name || 'No asignado' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-mono font-medium text-foreground">
                                            {{ gp.cupo_maximo }} estudiantes
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-10 border border-dashed rounded-lg">
                            <p class="text-muted-foreground text-sm">Este grupo no ha sido instanciado en ningún período académico.</p>
                            <p class="text-xs text-muted-foreground/80 mt-1">Para ofrecer este grupo, agréguelo desde el detalle del Período Académico.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
