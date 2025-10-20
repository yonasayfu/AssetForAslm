import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

type Direction = 'asc' | 'desc';

type ExtraParams = () => Record<string, unknown>;

type TableFilterOptions = {
    route: string;
    initial?: {
        search?: string;
        sort?: string;
        direction?: Direction;
        per_page?: number;
    };
    extra?: ExtraParams;
    debounce?: number;
};

type TableFilterState = {
    search: ReturnType<typeof ref<string>>;
    sort: ReturnType<typeof ref<string>>;
    direction: ReturnType<typeof ref<Direction>>;
    perPage: ReturnType<typeof ref<number>>;
    apply: () => void;
    toggleSort: (field: string) => void;
};

export function useTableFilters(options: TableFilterOptions): TableFilterState {
    const search = ref(options.initial?.search ?? '');
    const sort = ref(options.initial?.sort ?? '');
    const direction = ref<Direction>(options.initial?.direction ?? 'asc');
    const perPage = ref<number>(options.initial?.per_page ?? 10);

    const extra = computed(() => (options.extra ? options.extra() : {}));

    const run = useDebounceFn(() => {
        const params: Record<string, unknown> = {
            search: search.value || undefined,
            per_page: perPage.value,
        };

        if (sort.value) {
            params.sort = sort.value;
            params.direction = direction.value;
        }

        for (const [key, value] of Object.entries(extra.value)) {
            params[key] = value ?? undefined;
        }

        router.get(options.route, params, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }, options.debounce ?? 400);

    watch([search, sort, direction, perPage], () => run());

    const apply = () => run();

    const toggleSort = (field: string) => {
        if (sort.value === field) {
            direction.value = direction.value === 'asc' ? 'desc' : 'asc';
        } else {
            sort.value = field;
            direction.value = 'asc';
        }

        run();
    };

    return {
        search,
        sort,
        direction,
        perPage,
        apply,
        toggleSort,
    };
}
