<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { NotebookText, ShoppingCart, House, MonitorSmartphone, CreditCard, Contact, BookUser, Album, MessageSquare, Calendar, Clock, Bell, FileText, Forward } from 'lucide-vue-next';
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
                href: '/contacts',
                icon: Contact,
            },
            {
                title: 'Group Whatsapp',
                href: '/group-contacts',
                icon: BookUser,
            },
            {
                title: 'Group Contact',
                href: '/group-contacts',
                icon: BookUser,
            }
        ]
    },
        {
        title: 'Message',
        icon: MessageSquare,
        children: [
            {
                title: 'New Message',
                href: '/message/new',
                icon: FileText,
            },
            {
                title: 'Schedule',
                href: '/message/schedule',
                icon: Calendar,
            },
            {
                title: 'Auto Responder',
                href: '/message/auto-responder',
                icon: Clock,
            },
            {
                title: 'Reminder',
                href: '/message/reminder',
                icon: Bell,
            },
            {
                title: 'Auto Reply',
                href: '/message/auto-reply',
                icon: MessageSquare,
            },
            {
                title: 'Quick Reply',
                href: '/message/quick-reply',
                icon: MessageSquare,
            },
            {
                title: 'Forward Message',
                href: '/message/forward',
                icon: Forward,
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
