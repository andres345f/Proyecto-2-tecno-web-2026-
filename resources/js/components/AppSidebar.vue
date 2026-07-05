<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { useContadorVisitas } from '@/composables/useContadorVisitas';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BarChart3, BookOpen, ClipboardList, CreditCard, Folder, GraduationCap, Home, LayoutGrid, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const { currentVisits } = useContadorVisitas();

const mainNavItems = computed<NavItem[]>(() => {
    if (page.props.auth.has_overdue) {
        return [{ title: 'Mis Pagos', href: route('pagos.index'), icon: CreditCard }];
    }

    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: route('dashboard'),
            icon: LayoutGrid,
        },
    ];

    if (!user.value) return items;

    // Propietario
    if (user.value.is_propietario) {
        items.push(
            { title: 'Reportes', href: route('reportes.index'), icon: BarChart3 },
            { title: 'Aulas', href: route('aulas.index'), icon: Home },
            { title: 'Materias', href: route('materias.index'), icon: BookOpen },
            { title: 'Ofertas Académicas', href: route('ofertas-academicas.index'), icon: GraduationCap },
            { title: 'Grupos', href: route('grupos.index'), icon: Users },
            { title: 'Pagos', href: route('pagos.index'), icon: CreditCard },
            { title: 'Usuarios', href: route('usuarios.index'), icon: Users },
        );
    }

    // Director
    if (user.value.is_director) {
        items.push(
            { title: 'Reportes', href: route('reportes.index'), icon: BarChart3 },
            { title: 'Aulas', href: route('aulas.index'), icon: Home },
            { title: 'Materias', href: route('materias.index'), icon: BookOpen },
            { title: 'Ofertas Académicas', href: route('ofertas-academicas.index'), icon: GraduationCap },
            { title: 'Grupos', href: route('grupos.index'), icon: Users },
            { title: 'Usuarios', href: route('usuarios.index'), icon: Users },
            { title: 'Inscripciones', href: route('matriculas.index'), icon: ClipboardList },
        );
    }

    // Secretaria
    if (user.value.is_secretaria) {
        items.push(
            { title: 'Inscripciones', href: route('matriculas.index'), icon: ClipboardList },
            { title: 'Pagos', href: route('pagos.index'), icon: CreditCard },
            { title: 'Reportes', href: route('reportes.index'), icon: BarChart3 },
            { title: 'Ofertas Académicas', href: route('ofertas-academicas.index'), icon: GraduationCap },
        );
    }

    // Profesor
    if (user.value.is_profesor) {
        items.push({ title: 'Mis Grupos', href: route('grupos.docente.index'), icon: Users });
    }

    // Estudiante
    if (user.value.is_estudiante) {
        items.push(
            { title: 'Mis Grupos', href: route('mis-grupos'), icon: Users },
            { title: 'Mi Malla Curricular', href: route('malla-curricular.estudiante'), icon: GraduationCap },
            { title: 'Mis Pagos', href: route('pagos.index'), icon: CreditCard },
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="page.props.auth.has_overdue ? route('pagos.index') : route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <div
                class="mx-2 mb-2 flex items-center justify-between rounded-lg border border-sidebar-border bg-sidebar-accent/50 px-4 py-2 text-[11px] text-sidebar-foreground/80 group-data-[collapsible=icon]:hidden"
            >
                <span class="font-medium">Vistas de esta página:</span>
                <span
                    class="bg-sidebar-background rounded border border-sidebar-border/80 px-1.5 py-0.5 font-mono font-bold text-sidebar-foreground"
                    >{{ currentVisits ?? '...' }}</span
                >
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
