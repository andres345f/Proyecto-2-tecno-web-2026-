import { ref } from 'vue';

const currentVisits = ref<number | null>(null);

export function useContadorVisitas() {
    function registrarVisita(url: string) {
        const cleanUrl = url.split('?')[0].split('#')[0];

        fetch(route('api.visitas.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ url: cleanUrl }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && typeof data.visits_count === 'number') {
                currentVisits.value = data.visits_count;
            }
        })
        .catch(() => {});
    }

    return {
        registrarVisita,
        currentVisits
    };
}
