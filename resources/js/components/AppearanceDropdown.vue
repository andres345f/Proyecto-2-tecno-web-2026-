<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import { Baby, Briefcase, Check, Leaf, Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    align?: 'start' | 'center' | 'end';
}

withDefaults(defineProps<Props>(), {
    align: 'end',
});

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Claro' },
    { value: 'dark', Icon: Moon, label: 'Oscuro' },
    { value: 'kids', Icon: Baby, label: 'Infantil' },
    { value: 'elegant', Icon: Briefcase, label: 'Adultos' },
    { value: 'forest', Icon: Leaf, label: 'Jóvenes' },
    { value: 'system', Icon: Monitor, label: 'Sistema' },
] as const;

const currentIcon = computed(() => {
    return tabs.find((tab) => tab.value === appearance.value)?.Icon || Monitor;
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-9 w-9 cursor-pointer">
                <component :is="currentIcon" class="size-5 opacity-80" />
                <span class="sr-only">Cambiar apariencia</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent :align="align" class="w-40">
            <DropdownMenuItem
                v-for="{ value, Icon, label } in tabs"
                :key="value"
                @click="updateAppearance(value)"
                class="flex cursor-pointer items-center justify-between"
            >
                <div class="flex items-center">
                    <component :is="Icon" class="mr-2 h-4 w-4" />
                    <span>{{ label }}</span>
                </div>
                <Check v-if="appearance === value" class="h-4 w-4 opacity-80" />
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
