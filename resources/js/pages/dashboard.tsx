import GenericDashboard from '@/components/dashboard/GenericDashboard';
// import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard(props: any) {
    return (
        <GenericDashboard
            {...props}
            breadcrumbs={breadcrumbs}
            routeUrl="/dashboard"
        />
    );
}
