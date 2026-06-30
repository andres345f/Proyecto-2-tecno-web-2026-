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
    descripcion: string | null;
    materias_count: number;
}

const props = defineProps<{
    ofertas: OfertaAcademica[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ofertas Académicas', href: '/ofertas-academicas' },
];

const confirmDelete = (oferta: OfertaAcademica) => {
    if (confirm(`¿Eliminar oferta "${oferta.nombre}"?`)) {
        router.delete(`/ofertas-academicas/${oferta.id}`);
    }
};
</script>

<template>
    <Head title="Ofertas Académicas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Ofertas Académicas</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión de carreras y programas académicos vigentes.</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link href="/periodos-academicos">Períodos Académicos</Link>
                    </Button>
                    <Button as-child>
                        <Link href="/ofertas-academicas/create">Crear Oferta</Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de Ofertas Académicas</CardTitle>
                    <CardDescription>Visualiza las carreras y planes de estudio activos.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Nombre</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Código</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Materias en Malla</th>
                                    <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="oferta in ofertas" :key="oferta.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground">
                                        <Link :href="`/ofertas-academicas/${oferta.id}`" class="hover:underline text-primary">
                                            {{ oferta.nombre }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground font-mono">{{ oferta.codigo }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ oferta.materias_count }} materias</td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/ofertas-academicas/${oferta.id}`">Ver</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/ofertas-academicas/${oferta.id}/edit`">Editar</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmDelete(oferta)">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="ofertas.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay ofertas académicas registradas en el sistema.
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

