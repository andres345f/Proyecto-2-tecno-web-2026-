<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    BarChart3,
    Home,
    BookOpen,
    GraduationCap,
    Users,
    ClipboardList,
    Calendar,
    CreditCard,
    Folder
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';
import { useContadorVisitas } from '@/composables/useContadorVisitas';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const { currentVisits } = useContadorVisitas();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
    ];

    if (!user.value) return items;

    // Propietario / Director
    if (user.value.is_propietario || user.value.is_director) {
        items.push(
            { title: 'Reportes', href: '/reportes', icon: BarChart3 },
            { title: 'Aulas', href: '/aulas', icon: Home },
            { title: 'Materias', href: '/materias', icon: BookOpen },
            { title: 'Ofertas Académicas', href: '/ofertas-academicas', icon: GraduationCap },
            { title: 'Grupos', href: '/grupos', icon: Users },
            { title: 'Pagos', href: '/pagos', icon: CreditCard },
            { title: 'Usuarios', href: '/usuarios', icon: Users },
            { title: 'Inscripciones', href: '/matriculas', icon: ClipboardList }
        );
    }

    // Secretaria
    if (user.value.is_secretaria) {
        items.push(
            { title: 'Usuarios', href: '/usuarios', icon: Users },
            { title: 'Matrículas', href: '/matriculas', icon: ClipboardList },
            { title: 'Pagos', href: '/pagos', icon: CreditCard }
        );
        if (!items.some(i => i.href === '/aulas')) {
            items.push({ title: 'Aulas', href: '/aulas', icon: Home });
        }
        if (!items.some(i => i.href === '/materias')) {
            items.push({ title: 'Materias', href: '/materias', icon: BookOpen });
        }
    }

    // Profesor
    if (user.value.is_profesor) {
        items.push({ title: 'Mis Grupos', href: '/grupos-docente', icon: Users });
    }

    // Estudiante
    if (user.value.is_estudiante) {
        items.push(
            { title: 'Mis Grupos', href: '/mis-grupos', icon: Users },
            { title: 'Mi Malla Curricular', href: '/malla-curricular', icon: GraduationCap },
            { title: 'Mis Pagos', href: '/pagos', icon: CreditCard }
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
                        <Link :href="route('dashboard')">
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
                class="px-4 py-2 mx-2 mb-2 rounded-lg bg-muted/40 border border-border/50 text-[11px] text-muted-foreground flex items-center justify-between group-data-[collapsible=icon]:hidden">
                <span class="font-medium">Vistas de esta página:</span>
                <span
                    class="font-mono font-bold text-foreground bg-background px-1.5 py-0.5 rounded border border-border/40">{{
                        currentVisits ?? '...' }}</span>
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
