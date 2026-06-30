<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { ref, watch } from 'vue';

interface Usuario {
    id: number;
    name: string;
    email: string;
}

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
}

interface MatriculaCarrera {
    id: number;
    fecha_matricula: string;
    estado: string;
    usuario: Usuario;
    oferta_academica: OfertaAcademica;
}

const props = defineProps<{
    matriculas: {
        data: MatriculaCarrera[];
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
    filters?: {
        search?: string;
        estado?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Matrículas de Carrera', href: '/matriculas-carrera' },
];

const searchQuery = ref(props.filters?.search || '');
const selectedEstado = ref(props.filters?.estado || '');

let timeout: any = null;
watch([searchQuery, selectedEstado], () => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/matriculas-carrera', {
            search: searchQuery.value,
            estado: selectedEstado.value
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const estadoBadge = (estado: string) => {
    switch (estado) {
        case 'activo':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'inactivo':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'retirado':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-secondary text-secondary-foreground';
    }
};

const confirmDelete = (matricula: MatriculaCarrera) => {
    if (confirm(`¿Eliminar matrícula de ${matricula.usuario?.name} en ${matricula.oferta_academica?.nombre}?`)) {
        router.delete(`/matriculas-carrera/${matricula.id}`);
    }
};
</script>

<template>

    <Head title="Matrículas de Carrera" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Matrículas de Carrera</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión y registro de inscripciones de estudiantes a ofertas académicas.</p>
                </div>
                <Button as-child>
                    <Link href="/matriculas-carrera/create">Nueva Matrícula</Link>
                </Button>
            </div>

            <!-- Success Alert -->
            <div v-if="$page.props.flash?.success"
                class="p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-sm text-green-600 dark:text-green-400">
                {{ $page.props.flash.success }}
            </div>

            <!-- Filters Bar -->
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-card p-4 rounded-lg border border-border shadow-sm">
                <div class="w-full md:w-80 relative">
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Buscar estudiante o carrera..."
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground"
                    />
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label for="filter_estado" class="text-xs font-semibold text-muted-foreground whitespace-nowrap">Estado:</label>
                    <select
                        id="filter_estado"
                        v-model="selectedEstado"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground w-full md:w-40"
                    >
                        <option value="">Todos</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="retirado">Retirado</option>
                    </select>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de Inscripciones</CardTitle>
                    <CardDescription>Estudiantes matriculados formalmente en programas académicos.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Estudiante</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Carrera / Oferta</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Fecha Matrícula</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Estado</th>
                                    <th
                                        class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="matricula in matriculas.data" :key="matricula.id"
                                    class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground">
                                        {{ matricula.usuario?.name }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ matricula.oferta_academica?.nombre }} ({{ matricula.oferta_academica?.codigo }})
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">
                                        {{ matricula.fecha_matricula }}
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider',
                                            estadoBadge(matricula.estado),
                                        ]">
                                            {{ matricula.estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/matriculas-carrera/${matricula.id}`">Ver</Link>
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="matriculas.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">No hay matrículas registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="matriculas.links && matriculas.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                :href="matriculas.prev_page_url || '#'"
                                class="relative inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !matriculas.prev_page_url }"
                            >
                                Anterior
                            </Link>
                            <Link
                                :href="matriculas.next_page_url || '#'"
                                class="relative ml-3 inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !matriculas.next_page_url }"
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Mostrando del
                                    <span class="font-medium text-foreground">{{ matriculas.from || 0 }}</span>
                                    al
                                    <span class="font-medium text-foreground">{{ matriculas.to || 0 }}</span>
                                    de
                                    <span class="font-medium text-foreground">{{ matriculas.total || 0 }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in matriculas.links"
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
                                            i === matriculas.links.length - 1 ? 'rounded-r-md' : ''
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
