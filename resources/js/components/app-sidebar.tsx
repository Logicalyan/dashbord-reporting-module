import { Link } from '@inertiajs/react';
import { BookOpen, BuildingIcon, CalendarIcon, Folder, LayoutDashboardIcon, TableIcon } from 'lucide-react';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
// import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import AppLogo from './app-logo';

const menuItems = [
    {
        title: 'Attendance',
        icon: CalendarIcon,
        children: [
            {
                title: 'Dashboard',
                href: '/attendance/dashboard',
                icon: LayoutDashboardIcon,
            },
            {
                title: 'Records',
                href: '/attendance',
                icon: TableIcon,
            },
        ],
    },
    {
        title: 'Companies',
        icon: BuildingIcon,
        children: [
            {
                title: 'Dashboard',
                href: '/companies/dashboard',
                icon: LayoutDashboardIcon,
            },
            {
                title: 'List',
                href: '/companies',
                icon: TableIcon,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={menuItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
