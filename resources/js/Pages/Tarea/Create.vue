<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
}

const props = defineProps<{
    grupo: Grupo;
}>();

const form = useForm({
    titulo: '',
    descripcion: '',
    fecha_vencimiento: '',
    puntaje_maximo: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Grupos', href: route('grupos.index') },
    { title: props.grupo.codigo, href: route('grupos.show', props.grupo.id) },
    { title: 'Tareas', href: route('grupos.tareas.index', props.grupo.id) },
    { title: 'Crear', href: route('grupos.tareas.create', props.grupo.id) },
];

const submit = () => {
    form.post(route('grupos.tareas.store', props.grupo.id));
};
</script>

<template>
    <Head title="Crear Tarea" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Crear Tarea</h1>
                    <p class="text-sm text-muted-foreground mt-1">{{ grupo.codigo }} — {{ grupo.materia?.nombre }}</p>
                </div>
                <Button variant="ghost" as-child>
                    <Link :href="route('grupos.tareas.index', grupo.id)">Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles de la Tarea</CardTitle>
                    <CardDescription>Configura los parámetros, descripción, fecha de vencimiento y puntaje de la asignación.</CardDescription>
                </CardHeader>
                <CardContent class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="titulo">Título de la Tarea *</Label>
                            <Input
                                id="titulo"
                                v-model="form.titulo"
                                type="text"
                                placeholder="ej. Examen parcial, Ensayo de investigación..."
                                required
                            />
                            <InputError :message="form.errors.titulo" />
                        </div>

                        <div class="space-y-2">
                            <Label for="descripcion">Descripción / Instrucciones</Label>
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="4"
                                placeholder="Escribe las instrucciones detalladas de la tarea aquí..."
                                class="flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                            />
                            <InputError :message="form.errors.descripcion" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="fecha_vencimiento">Fecha de Vencimiento *</Label>
                                <Input
                                    id="fecha_vencimiento"
                                    v-model="form.fecha_vencimiento"
                                    type="datetime-local"
                                    required
                                />
                                <InputError :message="form.errors.fecha_vencimiento" />
                            </div>

                            <div class="space-y-2">
                                <Label for="puntaje_maximo">Puntaje Máximo *</Label>
                                <Input
                                    id="puntaje_maximo"
                                    v-model="form.puntaje_maximo"
                                    type="number"
                                    min="1"
                                    max="100"
                                    step="0.01"
                                    required
                                />
                                <InputError :message="form.errors.puntaje_maximo" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 border-t border-border pt-6">
                            <Button type="submit" :disabled="form.processing">
                                Crear Tarea
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="route('grupos.tareas.index', grupo.id)">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
