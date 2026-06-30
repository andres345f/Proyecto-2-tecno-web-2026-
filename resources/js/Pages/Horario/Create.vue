<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Aula {
    id: number;
    nombre: string;
    codigo: string;
}

interface Grupo {
    id: number;
    nombre: string;
}

const props = defineProps<{
    aulas: Aula[];
    grupos: Grupo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Horarios', href: '/horarios' },
    { title: 'Crear', href: '/horarios/create' },
];

const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

const form = useForm({
    dia: '',
    hora_inicio: '',
    hora_fin: '',
    aula_id: '',
    grupo_id: '',
});

const submit = () => {
    form.post('/horarios');
};
</script>

<template>
    <Head title="Crear Horario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Crear Horario</h1>
                <Link href="/horarios" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"> Volver </Link>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="dia" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Día</label>
                        <select
                            id="dia"
                            v-model="form.dia"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="">Seleccionar día</option>
                            <option v-for="dia in dias" :key="dia" :value="dia">{{ dia }}</option>
                        </select>
                        <p v-if="form.errors.dia" class="mt-1 text-sm text-red-600">{{ form.errors.dia }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="hora_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hora Inicio</label>
                            <input
                                id="hora_inicio"
                                v-model="form.hora_inicio"
                                type="time"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required
                            />
                            <p v-if="form.errors.hora_inicio" class="mt-1 text-sm text-red-600">{{ form.errors.hora_inicio }}</p>
                        </div>

                        <div>
                            <label for="hora_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hora Fin</label>
                            <input
                                id="hora_fin"
                                v-model="form.hora_fin"
                                type="time"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required
                            />
                            <p v-if="form.errors.hora_fin" class="mt-1 text-sm text-red-600">{{ form.errors.hora_fin }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="aula_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Aula</label>
                        <select
                            id="aula_id"
                            v-model="form.aula_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="">Seleccionar aula</option>
                            <option v-for="aula in aulas" :key="aula.id" :value="aula.id">{{ aula.nombre }} ({{ aula.codigo }})</option>
                        </select>
                        <p v-if="form.errors.aula_id" class="mt-1 text-sm text-red-600">{{ form.errors.aula_id }}</p>
                    </div>

                    <div>
                        <label for="grupo_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grupo (opcional)</label>
                        <select
                            id="grupo_id"
                            v-model="form.grupo_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Sin grupo</option>
                            <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">{{ grupo.nombre }}</option>
                        </select>
                        <p v-if="form.errors.grupo_id" class="mt-1 text-sm text-red-600">{{ form.errors.grupo_id }}</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                        >
                            Crear Horario
                        </button>
                        <Link href="/horarios" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
