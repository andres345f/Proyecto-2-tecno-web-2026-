<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

interface HorarioItem {
    dia: string;
    hora_inicio: string;
    hora_fin: string;
    aula_nombre?: string;
    grupo_codigo?: string;
    materia_nombre?: string;
    docente_name?: string;
}

const props = defineProps<{
    title: string;
    horarios: HorarioItem[];
    // To specify the layout target context (e.g., 'aula' to hide classroom info, 'usuario' to show it)
    type: 'aula' | 'usuario';
}>();

const days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

// Clean up time string (e.g. "08:00:00" -> "08:00")
const formatTime = (timeStr: string) => {
    if (!timeStr) return '';
    return timeStr.slice(0, 5);
};

// Get unique time slots sorted chronologically
const timeSlots = computed(() => {
    const slotsMap = new Map<string, { start: string; end: string }>();
    
    props.horarios.forEach(h => {
        const start = formatTime(h.hora_inicio);
        const end = formatTime(h.hora_fin);
        if (start && end) {
            const key = `${start} - ${end}`;
            slotsMap.set(key, { start, end });
        }
    });

    // Sort chronologically by start time
    return Array.from(slotsMap.values()).sort((a, b) => a.start.localeCompare(b.start));
});

// Helper to check if a schedule belongs to a cell
const getCellHorarios = (day: string, slot: { start: string; end: string }) => {
    return props.horarios.filter(h => {
        return h.dia.toLowerCase() === day.toLowerCase() &&
               formatTime(h.hora_inicio) === slot.start &&
               formatTime(h.hora_fin) === slot.end;
    });
};
</script>

<template>
    <Card class="border-border bg-card">
        <CardHeader class="pb-3">
            <CardTitle class="text-xl font-bold tracking-tight text-foreground">{{ title }}</CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="horarios.length === 0" class="py-8 text-center text-muted-foreground italic">
                Sin horarios asignados para mostrar.
            </div>
            
            <div v-else class="overflow-x-auto rounded-lg border border-border">
                <table class="w-full min-w-[800px] border-collapse text-sm">
                    <thead>
                        <tr class="bg-muted/50 border-b border-border">
                            <th class="h-12 w-28 px-4 text-center align-middle font-semibold text-muted-foreground uppercase tracking-wider border-r border-border">
                                Horario
                            </th>
                            <th 
                                v-for="day in days" 
                                :key="day" 
                                class="h-12 px-4 text-center align-middle font-semibold text-muted-foreground uppercase tracking-wider border-r border-border last:border-r-0"
                            >
                                {{ day }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="slot in timeSlots" :key="`${slot.start}-${slot.end}`" class="hover:bg-muted/10 transition-colors">
                            <!-- Time block label -->
                            <td class="px-3 py-4 text-center font-mono font-semibold text-foreground border-r border-border bg-muted/20">
                                <div class="text-sm">{{ slot.start }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5">a</div>
                                <div class="text-sm">{{ slot.end }}</div>
                            </td>
                            
                            <!-- Day cells -->
                            <td 
                                v-for="day in days" 
                                :key="day" 
                                class="px-2 py-3 border-r border-border last:border-r-0 align-top min-h-[80px]"
                            >
                                <div class="flex flex-col gap-2">
                                    <div 
                                        v-for="(h, idx) in getCellHorarios(day, slot)" 
                                        :key="idx"
                                        class="p-2.5 rounded-lg border text-xs shadow-xs transition-all hover:shadow-md"
                                        :class="[
                                            type === 'aula' 
                                                ? 'bg-blue-500/10 text-blue-500 border-blue-500/20' 
                                                : 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20'
                                        ]"
                                    >
                                        <div class="font-bold tracking-wide uppercase text-[10px] mb-1">
                                            {{ h.grupo_codigo }}
                                        </div>
                                        <div class="font-semibold text-foreground leading-tight mb-1 line-clamp-2">
                                            {{ h.materia_nombre }}
                                        </div>
                                        
                                        <!-- Conditionally show classroom or teacher based on context -->
                                        <div v-if="type === 'usuario' && h.aula_nombre" class="text-muted-foreground font-medium flex items-center mt-1">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5"></span>
                                            Aula: {{ h.aula_nombre }}
                                        </div>
                                        <div v-if="type === 'aula' && h.docente_name" class="text-muted-foreground font-medium flex items-center mt-1">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                            Doc: {{ h.docente_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>
