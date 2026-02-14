import { Head } from '@inertiajs/react';
import { Building2, TrendingUp, Calendar } from 'lucide-react';
import GenericDashboard from '@/components/dashboard/GenericDashboard';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Companies', href: '/companies' },
    { title: 'Dashboard', href: '/companies/dashboard' },
];

type NewestCompany = {
    name: string;
    email: string;
    status: string;
    joined_at: string;
    days_ago: number;
};

type GrowthRate = {
    current_period: number;
    previous_period: number;
    growth_rate: number;
};

type Props = {
    // From GenericDashboard
    activeTab: string;
    kpiMetrics: any[];
    chartConfigs: any;
    filterPresets?: any[];
    comparisonPresets?: any[];

    // Overview Tab
    filters?: any;
    summary?: Record<string, number>;
    growth?: number;
    charts?: any;
    newestCompanies?: NewestCompany[];
    growthRate?: GrowthRate;

    // Compare Tab
    compareFilters?: any;
    comparisons?: any;
    comparisonCharts?: any;
};

export default function CompanyDashboard(props: Props) {
    const {
        newestCompanies = [],
        growthRate,
        activeTab,
    } = props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company Dashboard" />

            <GenericDashboard
                {...props}
                breadcrumbs={breadcrumbs}
                routeUrl="/companies/dashboard"
            >
                {/* Custom Widgets for Overview Tab */}
                {activeTab === 'overview' && growthRate && (
                    <div className="grid gap-4 md:grid-cols-3 mt-6">
                        {/* Current Period Card */}
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Current Period
                                </CardTitle>
                                <Building2 className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {growthRate.current_period}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    New companies joined
                                </p>
                            </CardContent>
                        </Card>

                        {/* Previous Period Card */}
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Previous Period
                                </CardTitle>
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {growthRate.previous_period}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Same duration before
                                </p>
                            </CardContent>
                        </Card>

                        {/* Growth Rate Card */}
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Growth Rate
                                </CardTitle>
                                <TrendingUp className={`h-4 w-4 ${
                                    growthRate.growth_rate >= 0 ? 'text-green-500' : 'text-red-500'
                                }`} />
                            </CardHeader>
                            <CardContent>
                                <div className={`text-2xl font-bold ${
                                    growthRate.growth_rate >= 0 ? 'text-green-600' : 'text-red-600'
                                }`}>
                                    {growthRate.growth_rate > 0 ? '+' : ''}
                                    {growthRate.growth_rate}%
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Compared to previous period
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Newest Companies Table (Overview Tab) */}
                {activeTab === 'overview' && newestCompanies.length > 0 && (
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>Recently Joined Companies</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {newestCompanies.map((company, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between p-3 rounded-lg border"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 text-white font-bold">
                                                {company.name.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <div className="font-medium">
                                                    {company.name}
                                                </div>
                                                <div className="text-sm text-muted-foreground">
                                                    {company.email}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <div className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                                company.status === 'Active'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                            }`}>
                                                {company.status}
                                            </div>
                                            <div className="text-xs text-muted-foreground mt-1">
                                                {company.days_ago} days ago
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </GenericDashboard>
        </AppLayout>
    );
}
