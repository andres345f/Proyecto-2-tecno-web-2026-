<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
    periodo_academico?: { nombre: string }; // Made optional in case relation is loaded differently
    docente?: { name: string };
}

const props = defineProps<{
    grupos: {
        data: Grupo[];
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
    { title: 'Grupos', href: route('grupos.index') },
];

const search = ref(props.filters?.search || '');

let timeout: any = null;
watch(search, (newVal) => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('grupos.index'), { search: newVal }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const confirmDelete = (grupo: Grupo) => {
    if (confirm(`¿Eliminar grupo "${grupo.codigo}"?`)) {
        router.delete(route('grupos.destroy', grupo.id));
    }
};
</script>

<template>

    <Head title="Grupos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Grupos</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión de grupos escolares, horarios, materias y
                        docentes.</p>
                </div>
                <Button as-child>
                    <Link :href="route('grupos.create')">Crear Grupo</Link>
                </Button>
            </div>

            <Card>
                <CardHeader
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-border pb-6">
                    <div>
                        <CardTitle>Listado de Grupos</CardTitle>
                        <CardDescription>Visualiza los grupos asignados para el periodo académico actual.
                        </CardDescription>
                    </div>
                    <div class="w-full md:w-72">
                        <Input v-model="search" placeholder="Buscar grupo, materia o docente..." />
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
                                        Materia</th>

                                    <th
                                        class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="grupo in grupos.data" :key="grupo.id"
                                    class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground font-mono">{{
                                        grupo.codigo }}</td>
                                    <td class="px-6 py-4 align-middle text-foreground font-medium">
                                        <div class="flex flex-col">
                                            <span>{{ grupo.materia?.nombre }}</span>
                                            <span class="text-xs text-muted-foreground font-mono">{{
                                                grupo.materia?.codigo }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('grupos.show', grupo.id)">Ver</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('grupos.edit', grupo.id)">Editar</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmDelete(grupo)">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="grupos.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay grupos registrados en el sistema.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="grupos.links && grupos.links.length > 3"
                        class="flex items-center justify-between border-t border-border px-6 py-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link :href="grupos.prev_page_url || '#'"
                                class="relative inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !grupos.prev_page_url }">
                                Anterior
                            </Link>
                            <Link :href="grupos.next_page_url || '#'"
                                class="relative ml-3 inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !grupos.next_page_url }">
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Mostrando del
                                    <span class="font-medium text-foreground">{{ grupos.from || 0 }}</span>
                                    al
                                    <span class="font-medium text-foreground">{{ grupos.to || 0 }}</span>
                                    de
                                    <span class="font-medium text-foreground">{{ grupos.total || 0 }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                                    aria-label="Pagination">
                                    <Link v-for="(link, i) in grupos.links" :key="i" :href="link.url || '#'"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-semibold focus:z-20"
                                        :class="[
                                            link.active
                                                ? 'z-10 bg-primary text-primary-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary'
                                                : 'text-foreground ring-1 ring-inset ring-border hover:bg-muted/50 focus:outline-offset-0',
                                            !link.url ? 'opacity-50 pointer-events-none' : '',
                                            i === 0 ? 'rounded-l-md' : '',
                                            i === grupos.links.length - 1 ? 'rounded-r-md' : ''
                                        ]" />
                                </nav>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
