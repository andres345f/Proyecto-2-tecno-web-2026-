<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ClipboardList, Calendar, Users, ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    stats: {
        total_carreras: number;
        total_periodos: number;
        total_grupos: number;
    };
}>();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Matrículas', href: route('matriculas.index') },
];
</script>

<template>
    <Head title="Gestión de Matrículas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Gestión de Matrículas</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Central de inscripción académica. Matricula alumnos en ofertas, periodos y grupos de materias.
                </p>
            </div>

            <div :class="[
                'grid gap-6 mt-4',
                user?.is_director ? 'md:grid-cols-1 max-w-md w-full mx-auto' : 'md:grid-cols-3'
            ]">
                <!-- Carrera Enrollment Card -->
                <Card v-if="!user?.is_director" class="relative overflow-hidden group hover:shadow-lg transition-all duration-300 border-border bg-card">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500">
                                <ClipboardList class="h-6 w-6" />
                            </div>
                            <span class="text-2xl font-bold tracking-tight text-foreground">{{ stats.total_carreras }}</span>
                        </div>
                        <CardTitle class="mt-4 text-xl">Matrícula de Carrera</CardTitle>
                        <CardDescription class="mt-2 min-h-[40px]">
                            Inscribe a nuevos estudiantes a una carrera u Oferta Académica disponible.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-4 flex justify-end">
                        <Button as-child variant="default" class="w-full justify-between">
                            <Link :href="route('matriculas-carrera.index')">
                                Gestionar Carreras
                                <ArrowRight class="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </Button>
                    </CardContent>
                </Card>

                <!-- Period Enrollment Card -->
                <Card v-if="!user?.is_director" class="relative overflow-hidden group hover:shadow-lg transition-all duration-300 border-border bg-card">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-500">
                                <Calendar class="h-6 w-6" />
                            </div>
                            <span class="text-2xl font-bold tracking-tight text-foreground">{{ stats.total_periodos }}</span>
                        </div>
                        <CardTitle class="mt-4 text-xl">Matrícula de Período</CardTitle>
                        <CardDescription class="mt-2 min-h-[40px]">
                            Inscribe estudiantes activos a un ciclo académico y define su plan de pagos.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-4 flex justify-end">
                        <Button as-child variant="default" class="w-full justify-between">
                            <Link :href="route('matriculas-periodo.index')">
                                Gestionar Períodos
                                <ArrowRight class="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </Button>
                    </CardContent>
                </Card>

                <!-- Group Enrollment Card -->
                <Card class="relative overflow-hidden group hover:shadow-lg transition-all duration-300 border-border bg-card">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <div class="p-2 rounded-lg bg-violet-500/10 text-violet-500">
                                <Users class="h-6 w-6" />
                            </div>
                            <span class="text-2xl font-bold tracking-tight text-foreground">{{ stats.total_grupos }}</span>
                        </div>
                        <CardTitle class="mt-4 text-xl">Inscripción de Grupo</CardTitle>
                        <CardDescription class="mt-2 min-h-[40px]">
                            Registra alumnos en materias y grupos específicos de forma controlada.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-4 flex justify-end">
                        <Button as-child variant="default" class="w-full justify-between">
                            <Link :href="route('matriculas-grupo.index')">
                                Gestionar Grupos
                                <ArrowRight class="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
