<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { ref, watch } from 'vue';

function debounce<T extends (...args: any[]) => void>(fn: T, delay: number): (...args: Parameters<T>) => void {
    let timeoutId: ReturnType<typeof setTimeout> | null = null;
    return (...args: Parameters<T>) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

interface Usuario {
    id: number;
    name: string;
    email: string;
    codigo_estudiante?: string | null;
    is_propietario: boolean;
    is_director: boolean;
    is_secretaria: boolean;
    is_profesor: boolean;
    is_estudiante: boolean;
    is_activo: boolean;
}

const props = defineProps<{
    usuarios: {
        data: Usuario[];
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
    filters: {
        search: string | null;
        role: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Usuarios', href: '/usuarios' },
];

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');

const updateFilters = debounce(() => {
    router.get(
        '/usuarios',
        { search: search.value, role: role.value },
        { preserveState: true, replace: true }
    );
}, 300);

watch([search, role], () => {
    updateFilters();
});

const deleteUsuario = (id: number) => {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        router.delete(`/usuarios/${id}`);
    }
};

const getRolesList = (usuario: Usuario) => {
    const list = [];
    if (usuario.is_propietario) list.push({ name: 'Propietario', color: 'bg-red-500/10 text-red-500 border-red-500/20' });
    if (usuario.is_director) list.push({ name: 'Director', color: 'bg-amber-500/10 text-amber-500 border-amber-500/20' });
    if (usuario.is_secretaria) list.push({ name: 'Secretaria', color: 'bg-blue-500/10 text-blue-500 border-blue-500/20' });
    if (usuario.is_profesor) list.push({ name: 'Docente', color: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' });
    if (usuario.is_estudiante) list.push({ name: 'Estudiante', color: 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20' });
    return list;
};
</script>

<template>

    <Head title="Administración de Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Usuarios</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión de estudiantes, profesores y personal
                        administrativo.</p>
                </div>
                <Button as-child>
                    <Link href="/usuarios/create">Crear Usuario</Link>
                </Button>
            </div>

            <Card>
                <CardHeader class="pb-3">
                    <CardTitle>Listado de Cuentas</CardTitle>
                    <CardDescription>Busca y filtra las cuentas registradas en el sistema.</CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- Filters Grid -->
                    <div class="flex flex-col md:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <Input v-model="search" type="text" placeholder="Buscar por nombre o email..."
                                class="w-full" />
                        </div>
                        <div class="w-full md:w-48">
                            <select v-model="role"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white">
                                <option value="">Todos los Roles</option>
                                <option value="propietario">Propietario</option>
                                <option value="director">Director</option>
                                <option value="secretaria">Secretaria</option>
                                <option value="profesor">Docente</option>
                                <option value="estudiante">Estudiante</option>
                            </select>
                        </div>
                    </div>

                    <!-- Desktop table view -->
                    <div class="overflow-x-auto rounded-md border border-border">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Nombre</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Email</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Roles</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Estado</th>
                                    <th
                                        class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="usuario in usuarios.data" :key="usuario.id"
                                    class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground">
                                        {{ usuario.name }}
                                        <p v-if="usuario.is_estudiante && usuario.codigo_estudiante" class="text-xs font-normal text-muted-foreground font-mono mt-0.5">
                                            Cód: {{ usuario.codigo_estudiante }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground font-mono">{{ usuario.email
                                        }}</td>
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="r in getRolesList(usuario)" :key="r.name"
                                                class="px-2 py-0.5 text-xs font-semibold rounded-full border"
                                                :class="r.color">
                                                {{ r.name }}
                                            </span>
                                            <span v-if="getRolesList(usuario).length === 0"
                                                class="text-xs text-muted-foreground italic">Sin Rol</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="usuario.is_activo ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500'">
                                            {{ usuario.is_activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/usuarios/${usuario.id}/edit`">Editar</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="deleteUsuario(usuario.id)"
                                            :disabled="$page.props.auth.user.id === usuario.id">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="usuarios.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground italic">
                                        No se encontraron usuarios que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="usuarios.links && usuarios.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4 mt-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                :href="usuarios.prev_page_url || '#'"
                                class="relative inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !usuarios.prev_page_url }"
                            >
                                Anterior
                            </Link>
                            <Link
                                :href="usuarios.next_page_url || '#'"
                                class="relative ml-3 inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !usuarios.next_page_url }"
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Mostrando del
                                    <span class="font-medium text-foreground">{{ usuarios.from || 0 }}</span>
                                    al
                                    <span class="font-medium text-foreground">{{ usuarios.to || 0 }}</span>
                                    de
                                    <span class="font-medium text-foreground">{{ usuarios.total || 0 }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in usuarios.links"
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
                                            i === usuarios.links.length - 1 ? 'rounded-r-md' : ''
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
