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
    descripcion: string | null;
}

const props = defineProps<{
    materia: Materia;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Materias', href: route('materias.index') },
    { title: 'Editar', href: route('materias.edit', props.materia.id) },
];

const form = useForm({
    nombre: props.materia.nombre,
    codigo: props.materia.codigo,
    descripcion: props.materia.descripcion || '',
});

const submit = () => {
    form.put(route('materias.update', props.materia.id));
};
</script>

<template>
    <Head title="Editar Materia" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Editar Materia</h1>
                    <p class="text-sm text-muted-foreground mt-1">Actualiza los datos de la asignatura en el catálogo lectivo.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="route('materias.index')">Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles de la Materia</CardTitle>
                    <CardDescription>Define el código, nombre y descripción de la materia.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="nombre">Nombre</Label>
                            <Input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                placeholder="ej. Cálculo I, Introducción a la Programación"
                                required
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <div class="space-y-2">
                            <Label for="codigo">Código</Label>
                            <Input
                                id="codigo"
                                v-model="form.codigo"
                                type="text"
                                placeholder="ej. MAT-101, INF-111"
                                required
                            />
                            <InputError :message="form.errors.codigo" />
                        </div>

                        <div class="space-y-2">
                            <Label for="descripcion">Descripción</Label>
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="3"
                                placeholder="Escribe una breve descripción del contenido de la materia..."
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            ></textarea>
                            <InputError :message="form.errors.descripcion" />
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <Button type="submit" :disabled="form.processing">
                                Actualizar Materia
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="route('materias.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
