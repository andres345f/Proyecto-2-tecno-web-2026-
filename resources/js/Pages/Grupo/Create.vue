<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

interface Materia {
    id: number;
    nombre: string;
    codigo: string;
}

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
}

const props = defineProps<{
    materias: Materia[];
    oferta_id: number | string | null;
    oferta: OfertaAcademica | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Grupos', href: route('grupos.index') },
    { title: 'Crear', href: route('grupos.create') },
];

const form = useForm({
    codigo: '',
    materia_id: '',
    oferta_id: props.oferta_id || '',
});

const submit = () => {
    form.post(route('grupos.store'));
};
</script>

<template>
    <Head title="Crear Grupo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Crear Grupo</h1>
                    <p class="text-sm text-muted-foreground mt-1">Registra un nuevo grupo en el catálogo de asignaturas.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="oferta_id ? route('grupos.index', { oferta_id }) : route('grupos.index')">Volver</Link>
                </Button>
            </div>

            <Card class="border border-muted">
                <CardHeader>
                    <CardTitle>Detalles del Catálogo</CardTitle>
                    <CardDescription>
                        Define el código del grupo y la asignatura a la que pertenece. La asignación de docentes, cupos y horarios se realiza por cada período académico.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="codigo" class="text-sm font-semibold">Código del Grupo</Label>
                            <Input
                                id="codigo"
                                v-model="form.codigo"
                                type="text"
                                placeholder="ej. GRUPO-1, GRUPO-2"
                                required
                                class="bg-background"
                            />
                            <InputError :message="form.errors.codigo" />
                        </div>

                        <div class="space-y-2">
                            <Label for="materia_id" class="text-sm font-semibold">Materia / Asignatura</Label>
                            <select
                                id="materia_id"
                                v-model="form.materia_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                                required
                            >
                                <option value="">Seleccionar materia...</option>
                                <option v-for="materia in materias" :key="materia.id" :value="materia.id">
                                    {{ materia.nombre }} ({{ materia.codigo }})
                                </option>
                            </select>
                            <InputError :message="form.errors.materia_id" />
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <Button type="submit" :disabled="form.processing">
                                Crear Grupo
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="oferta_id ? route('grupos.index', { oferta_id }) : route('grupos.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
