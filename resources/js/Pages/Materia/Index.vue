<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

interface Materia {
    id: number;
    nombre: string;
    codigo: string;
    descripcion: string | null;
    prerrequisitos_count: number;
}

const props = defineProps<{
    materias: {
        data: Materia[];
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
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Materias', href: route('materias.index') },
];

const search = ref(props.filters?.search || '');

let timeout: any = null;
watch(search, (newVal) => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('materias.index'), { search: newVal }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});
</script>

<template>

    <Head title="Materias" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Materias</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión del catálogo global de asignaturas y sus
                        prerrequisitos.</p>
                </div>
                <Button as-child>
                    <Link :href="route('materias.create')">Crear Materia</Link>
                </Button>
            </div>

            <Card>
                <CardHeader
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-border pb-6">
                    <div>
                        <CardTitle>Listado de Materias</CardTitle>
                        <CardDescription>Visualiza las materias configuradas y la cantidad de prerrequisitos asignados.
                        </CardDescription>
                    </div>
                    <div class="w-full md:w-72">
                        <Input v-model="search" placeholder="Buscar materia por nombre o código..." />
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Código</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Nombre</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Prerrequisitos</th>
                                    <th
                                        class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="materia in materias.data" :key="materia.id"
                                    class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-mono font-medium text-foreground">{{
                                        materia.codigo }}</td>
                                    <td class="px-6 py-4 align-middle text-foreground font-semibold">{{ materia.nombre
                                        }}</td>
                                    <td class="px-6 py-4 align-middle">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-secondary text-secondary-foreground">
                                            {{ materia.prerrequisitos_count }} prerequisitos
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <!-- <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/materias/${materia.id}`">Ver</Link>
                                        </Button> -->
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('materias.edit', materia.id)">Editar</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" as-child>
                                            <Link :href="route('materias.destroy', materia.id)" method="delete" as="button"
                                                onclick="return confirm('¿Eliminar materia?');">
                                                Eliminar
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="materias.data.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay materias registradas en el sistema.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="materias.links && materias.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                :href="materias.prev_page_url || '#'"
                                class="relative inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !materias.prev_page_url }"
                            >
                                Anterior
                            </Link>
                            <Link
                                :href="materias.next_page_url || '#'"
                                class="relative ml-3 inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !materias.next_page_url }"
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Mostrando del
                                    <span class="font-medium text-foreground">{{ materias.from || 0 }}</span>
                                    al
                                    <span class="font-medium text-foreground">{{ materias.to || 0 }}</span>
                                    de
                                    <span class="font-medium text-foreground">{{ materias.total || 0 }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in materias.links"
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
                                            i === materias.links.length - 1 ? 'rounded-r-md' : ''
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
