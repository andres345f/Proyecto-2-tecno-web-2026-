<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

interface Aula {
    id: number;
    nombre: string;
    codigo: string;
    capacidad: number;
}

const props = defineProps<{
    aulas: {
        data: Aula[];
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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Aulas', href: '/aulas' },
];

const search = ref(props.filters?.search || '');

let timeout: any = null;
watch(search, (newVal) => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/aulas', { search: newVal }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const confirmDelete = (aula: Aula) => {
    if (confirm(`¿Eliminar aula "${aula.nombre}"?`)) {
        router.delete(`/aulas/${aula.id}`);
    }
};
</script>

<template>
    <Head title="Aulas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Aulas</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión de aulas físicas y ambientes de aprendizaje.</p>
                </div>
                <Button as-child>
                    <Link href="/aulas/create">Crear Aula</Link>
                </Button>
            </div>

            <Card>
                <CardHeader class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-border pb-6">
                    <div>
                        <CardTitle>Listado de Aulas</CardTitle>
                        <CardDescription>Visualiza las aulas registradas y sus capacidades físicas.</CardDescription>
                    </div>
                    <div class="w-full md:w-72">
                        <Input v-model="search" placeholder="Buscar aula por nombre o código..." />
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Nombre</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Código</th>
                                    <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Capacidad</th>
                                    <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="aula in aulas.data" :key="aula.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-medium text-foreground">{{ aula.nombre }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground font-mono">{{ aula.codigo }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ aula.capacidad }} personas</td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/aulas/${aula.id}`">Ver Horario</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/aulas/${aula.id}/edit`">Editar</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmDelete(aula)">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="aulas.data.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay aulas registradas en el sistema.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="aulas.links && aulas.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                :href="aulas.prev_page_url || '#'"
                                class="relative inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !aulas.prev_page_url }"
                            >
                                Anterior
                            </Link>
                            <Link
                                :href="aulas.next_page_url || '#'"
                                class="relative ml-3 inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !aulas.next_page_url }"
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Mostrando del
                                    <span class="font-medium text-foreground">{{ aulas.from || 0 }}</span>
                                    al
                                    <span class="font-medium text-foreground">{{ aulas.to || 0 }}</span>
                                    de
                                    <span class="font-medium text-foreground">{{ aulas.total || 0 }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in aulas.links"
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
                                            i === aulas.links.length - 1 ? 'rounded-r-md' : ''
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

