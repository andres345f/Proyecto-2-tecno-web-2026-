import axios from 'axios';
import { ref, watch } from 'vue';

interface SearchResult {
    id: number;
    nombre: string;
    tipo: 'student' | 'materia' | 'cuota';
    [key: string]: unknown;
}

interface SearchResults {
    usuarios: SearchResult[];
    materias: SearchResult[];
    cuotas: SearchResult[];
}

export function useBuscadorGlobal() {
    const query = ref('');
    const results = ref<SearchResults>({
        usuarios: [],
        materias: [],
        cuotas: [],
    });
    const isSearching = ref(false);
    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    const search = async (q: string) => {
        if (!q || q.length < 2) {
            results.value = { usuarios: [], materias: [], cuotas: [] };
            return;
        }

        isSearching.value = true;

        try {
            const response = await axios.get('/api/buscador-global', {
                params: { q },
            });
            results.value = response.data;
        } catch {
            results.value = { usuarios: [], materias: [], cuotas: [] };
        } finally {
            isSearching.value = false;
        }
    };

    watch(query, (newQuery) => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(() => {
            search(newQuery);
        }, 300);
    });

    const clearSearch = () => {
        query.value = '';
        results.value = { usuarios: [], materias: [], cuotas: [] };
    };

    return {
        query,
        results,
        isSearching,
        search,
        clearSearch,
    };
}
