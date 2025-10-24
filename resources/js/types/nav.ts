import type { LucideIcon } from 'lucide-vue-next'

export interface NavItem {
    title: string
    href: string
    icon?: LucideIcon | string
    description?: string
    badge?: string
    isActive?: boolean
    permission?: string | null
}
