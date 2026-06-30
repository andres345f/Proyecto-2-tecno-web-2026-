<script setup lang="ts">
defineProps<{
    searchQuery: string;
    selectedEstado: string;
    selectedPeriodo: string;
    selectedRendimiento: string;
    isStudent: boolean;
    periodosDisponibles: string[];
}>();

const emit = defineEmits<{
    'update:searchQuery': [value: string];
    'update:selectedEstado': [value: string];
    'update:selectedPeriodo': [value: string];
    'update:selectedRendimiento': [value: string];
}>();
</script>

<template>
    <div class="flex flex-col lg:flex-row gap-4 items-center justify-between bg-card p-4 rounded-lg border border-border shadow-sm">
        <!-- Search Input -->
        <div class="w-full lg:w-80 relative">
            <input
                type="text"
                :value="searchQuery"
                @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
                :placeholder="isStudent ? 'Buscar por grupo o materia...' : 'Buscar por grupo, materia, carrera...'"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground"
            />
        </div>

        <!-- Filters for Student -->
        <div v-if="isStudent" class="flex items-center gap-2 w-full lg:w-auto">
            <label for="filter_estado" class="text-xs font-semibold text-muted-foreground whitespace-nowrap">Estado:</label>
            <select
                id="filter_estado"
                :value="selectedEstado"
                @change="emit('update:selectedEstado', ($event.target as HTMLSelectElement).value)"
                class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground w-full md:w-40"
            >
                <option value="">Todos</option>
                <option value="inscrito">Inscrito</option>
                <option value="en_curso">En curso</option>
                <option value="aprobado">Aprobado</option>
                <option value="reprobado">Reprobado</option>
                <option value="retirado">Retirado</option>
            </select>
        </div>

        <!-- Filters for Admin/Secretary -->
        <div v-else class="flex flex-col md:flex-row items-center gap-4 w-full lg:w-auto">
            <!-- Period Filter -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <label for="filter_periodo" class="text-xs font-semibold text-muted-foreground whitespace-nowrap">Período:</label>
                <select
                    id="filter_periodo"
                    :value="selectedPeriodo"
                    @change="emit('update:selectedPeriodo', ($event.target as HTMLSelectElement).value)"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground w-full md:w-48"
                >
                    <option value="">Todos los períodos</option>
                    <option v-for="periodo in periodosDisponibles" :key="periodo" :value="periodo">
                        {{ periodo }}
                    </option>
                </select>
            </div>

            <!-- Grading Status Filter -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <label for="filter_rendimiento" class="text-xs font-semibold text-muted-foreground whitespace-nowrap">Calificación:</label>
                <select
                    id="filter_rendimiento"
                    :value="selectedRendimiento"
                    @change="emit('update:selectedRendimiento', ($event.target as HTMLSelectElement).value)"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground w-full md:w-40"
                >
                    <option value="">Todos</option>
                    <option value="con_notas">Con Notas</option>
                    <option value="sin_notas">Sin Notas</option>
                </select>
            </div>
        </div>
    </div>
</template>
