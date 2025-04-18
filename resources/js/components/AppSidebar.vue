<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { NotebookText, ShoppingCart, House , MonitorSmartphone, CreditCard, Contact, BookUser, Album  } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: House,
    },
    {
        title: 'Devices',
        href: '/devices',
        icon: MonitorSmartphone,
    },
    {
        title: 'Phonebook',
        icon: Album,
        children: [
            {
                title: 'Contact',
                href: '/subscriptions',
                icon: Contact,
            },
            {
                title: 'Group',
                href: '/subscriptions/invoice',
                icon: BookUser,
            }
        ]
    },
    {
        title: 'Payments',
        icon: CreditCard,
        children: [
            {
                title: 'Subscriptions',
                href: '/subscriptions',
                icon: ShoppingCart,
            },
            {
                title: 'Invoices',
                href: '/subscriptions/invoice',
                icon: NotebookText,
            }
        ]
    }
];

const footerNavItems: NavItem[] = [
//
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
