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

interface PeriodoAcademico {
    id: number;
    nombre: string;
    tipo: string;
    fecha_inicio: string;
    fecha_fin: string;
    oferta_academica: OfertaAcademica;
    estado: string;
    numero_maximo_materias?: number | null;
}

const props = defineProps<{
    periodos: PeriodoAcademico[];
    ofertas: OfertaAcademica[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Períodos Académicos', href: route('periodos-academicos.index') },
];

const confirmDelete = (periodo: PeriodoAcademico) => {
    if (confirm(`¿Eliminar período "${periodo.nombre}"?`)) {
        router.delete(route('periodos-academicos.destroy', periodo.id));
    }
};
</script>

<template>
    <Head title="Períodos Académicos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Períodos Académicos</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión de ciclos y calendarios académicos registrados.</p>
                </div>
                <Button as-child>
                    <Link :href="route('periodos-academicos.create')">Crear Período</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de Períodos Académicos</CardTitle>
                    <CardDescription>Visualiza los ciclos académicos, sus fechas de procesos y estados.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Nombre</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Tipo</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Oferta Académica</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Límite</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Inicio</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Fin</th>
                                    <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="periodo in periodos" :key="periodo.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground">{{ periodo.nombre }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground capitalize">{{ periodo.tipo }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ periodo.oferta_academica?.nombre }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ periodo.numero_maximo_materias ? `${periodo.numero_maximo_materias} mat.` : 'Sin límite' }}
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                            :class="{
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': periodo.estado === 'inscripcion',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300': periodo.estado === 'cierre',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300': periodo.estado === 'retiro',
                                                'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300': periodo.estado === 'terminado'
                                            }"
                                        >
                                            {{ periodo.estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground font-mono">
                                        {{ new Date(periodo.fecha_inicio).toLocaleDateString('es-ES') }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground font-mono">
                                        {{ new Date(periodo.fecha_fin).toLocaleDateString('es-ES') }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('periodos-academicos.show', periodo.id)">Ver</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('periodos-academicos.edit', periodo.id)">Editar</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmDelete(periodo)">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="periodos.length === 0">
                                    <td colspan="8" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay períodos académicos registrados.
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
