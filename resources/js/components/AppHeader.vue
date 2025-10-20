<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import GlassButton from '@/components/GlassButton.vue';
import GlassCard from '@/components/GlassCard.vue';
import GlobalSearch from '@/components/GlobalSearch.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { toUrl, urlIsActive } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';
import { InertiaLinkProps, Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Menu } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const isCurrentRoute = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        urlIsActive(url, page.url),
);

const activeItemStyles = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        isCurrentRoute.value(toUrl(url))
            ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100'
            : '',
);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

</script>

<template>
    <div class="relative bg-gradient-to-br from-slate-50 via-slate-100 to-white transition-colors dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <GlassCard variant="lite" padding="p-0" content-class="">
                <div class="flex h-16 w-full items-center gap-3 px-4 sm:px-6">
                    <div class="lg:hidden">
                        <Sheet>
                            <SheetTrigger :as-child="true">
                                <GlassButton
                                    size="sm"
                                    class="mr-2 h-9 w-9 justify-center p-0"
                                    aria-label="Open navigation"
                                >
                                    <Menu class="h-5 w-5" />
                                </GlassButton>
                            </SheetTrigger>
                            <SheetContent side="left" class="w-[300px] p-6">
                                <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                                <SheetHeader class="flex justify-start text-left">
                                    <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                                </SheetHeader>
                                <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                    <nav class="-mx-3 space-y-1">
                                        <Link
                                            v-for="item in mainNavItems"
                                            :key="item.title"
                                            :href="item.href"
                                            class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                            :class="activeItemStyles(item.href)"
                                        >
                                            <component
                                                v-if="item.icon"
                                                :is="item.icon"
                                                class="h-5 w-5"
                                            />
                                            {{ item.title }}
                                        </Link>
                                    </nav>
                                    <div class="flex flex-col space-y-4">
                                        <a
                                            v-for="item in rightNavItems"
                                            :key="item.title"
                                            :href="toUrl(item.href)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center space-x-2 text-sm font-medium"
                                        >
                                            <component
                                                v-if="item.icon"
                                                :is="item.icon"
                                                class="h-5 w-5"
                                            />
                                            <span>{{ item.title }}</span>
                                        </a>
                                    </div>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>

                    <Link :href="dashboard()" class="flex items-center gap-x-2">
                        <AppLogo />
                    </Link>

                    <div class="hidden h-full flex-1 lg:flex">
                        <NavigationMenu class="ml-10 flex h-full items-stretch">
                            <NavigationMenuList class="flex h-full items-stretch space-x-2">
                                <NavigationMenuItem
                                    v-for="item in mainNavItems"
                                    :key="item.title"
                                    class="relative flex h-full items-center justify-center"
                                >
                                    <Link
                                        class="relative flex items-center justify-center bg-transparent text-sm font-medium transition-colors duration-150"
                                        :class="[
                                            navigationMenuTriggerStyle(),
                                            activeItemStyles(item.href),
                                            'h-9 cursor-pointer px-3',
                                        ]"
                                        :href="item.href"
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="mr-2 h-4 w-4"
                                        />
                                        {{ item.title }}
                                    </Link>
                                    <div
                                        v-if="isCurrentRoute(item.href)"
                                        class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                    />
                                </NavigationMenuItem>
                            </NavigationMenuList>
                        </NavigationMenu>
                    </div>

                    <div class="ml-auto flex items-center space-x-2">
                        <div class="relative flex items-center space-x-2">
                            <GlobalSearch />

                            <div class="hidden space-x-1 lg:flex">
                                <template v-for="item in rightNavItems" :key="item.title">
                                    <TooltipProvider :delay-duration="0">
                                        <Tooltip>
                                            <TooltipTrigger :as-child="true">
                                                <GlassButton
                                                    as="a"
                                                    size="sm"
                                                    class="group h-9 w-9 justify-center p-0"
                                                    :href="toUrl(item.href)"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component
                                                        :is="item.icon"
                                                        class="size-5 opacity-80 group-hover:opacity-100"
                                                    />
                                                </GlassButton>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>{{ item.title }}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </template>
                            </div>
                        </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger :as-child="true">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="btn-glass relative size-10 w-auto justify-center rounded-full p-1 focus-within:ring-0 focus-visible:ring-0"
                                >
                                    <Avatar class="size-8 overflow-hidden rounded-full">
                                        <AvatarImage
                                            v-if="auth.user.avatar"
                                            :src="auth.user.avatar"
                                            :alt="auth.user.name"
                                        />
                                        <AvatarFallback
                                            class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ getInitials(auth.user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <UserMenuContent :user="auth.user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <div
                    v-if="props.breadcrumbs.length > 1"
                    class="border-t border-white/30 px-4 py-3 text-sm text-slate-600 dark:border-white/10 dark:text-slate-300 sm:px-6"
                >
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>
            </GlassCard>
        </div>
    </div>
</template>

