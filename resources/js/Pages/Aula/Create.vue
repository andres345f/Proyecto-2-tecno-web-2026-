<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Aulas', href: '/aulas' },
    { title: 'Crear', href: '/aulas/create' },
];

const form = useForm({
    nombre: '',
    codigo: '',
    capacidad: '',
});

const submit = () => {
    form.post('/aulas');
};
</script>

<template>
    <Head title="Crear Aula" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Crear Aula</h1>
                    <p class="text-sm text-muted-foreground mt-1">Registra una nueva aula física en el sistema.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/aulas">Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles del Aula</CardTitle>
                    <CardDescription>Ingresa la información básica para identificar y parametrizar el aula.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="nombre">Nombre</Label>
                            <Input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                placeholder="ej. Aula 101, Laboratorio de Física"
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
                                placeholder="ej. A-101, LAB-FIS"
                                required
                            />
                            <InputError :message="form.errors.codigo" />
                        </div>

                        <div class="space-y-2">
                            <Label for="capacidad">Capacidad</Label>
                            <Input
                                id="capacidad"
                                v-model="form.capacidad"
                                type="number"
                                min="1"
                                placeholder="ej. 30"
                                required
                            />
                            <InputError :message="form.errors.capacidad" />
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <Button type="submit" :disabled="form.processing">
                                Crear Aula
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link href="/aulas">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

