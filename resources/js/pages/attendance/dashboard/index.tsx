import { Head } from '@inertiajs/react';
import { Users, Clock, TrendingUp } from 'lucide-react';
import GenericDashboard from '@/components/dashboard/GenericDashboard';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Attendance', href: '/attendance' },
    { title: 'Dashboard', href: '/attendance/dashboard' },
];

type TopEmployee = {
    employee_name: string;
    position: string;
    total_days: number;
    total_hours: number;
    present_count: number;
    attendance_rate: number;
};

type OvertimeStats = {
    total_overtime: number;
    avg_overtime: number;
    employees_with_overtime: number;
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
    topEmployees?: TopEmployee[];
    avgHours?: number;
    overtimeStats?: OvertimeStats;

    // Compare Tab
    compareFilters?: any;
    comparisons?: any;
    comparisonCharts?: any;
    statusDistribution?: {
        period1: Record<string, number>;
        period2: Record<string, number>;
    };
};

export default function AttendanceDashboard(props: Props) {
    const {
        topEmployees = [],
        avgHours = 0,
        overtimeStats,
        statusDistribution,
        activeTab,
    } = props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Attendance Dashboard" />

            <GenericDashboard
                {...props}
                breadcrumbs={breadcrumbs}
                routeUrl="/attendance/dashboard"
            >
                {/* Custom Widgets for Overview Tab */}
                {activeTab === 'overview' && (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mt-6">
                        {/* Average Hours Card */}
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Average Hours/Day
                                </CardTitle>
                                <Clock className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{avgHours}</div>
                                <p className="text-xs text-muted-foreground">
                                    Per employee per day
                                </p>
                            </CardContent>
                        </Card>

                        {/* Overtime Stats Card */}
                        {overtimeStats && (
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        Total Overtime
                                    </CardTitle>
                                    <TrendingUp className="h-4 w-4 text-orange-500" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-orange-600">
                                        {overtimeStats.total_overtime}h
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {overtimeStats.employees_with_overtime} employees with OT
                                    </p>
                                </CardContent>
                            </Card>
                        )}

                        {/* Top Employees Card */}
                        <Card className="md:col-span-2 lg:col-span-1">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Employees
                                </CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {topEmployees.length}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Active in this period
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Top Performers Table (Overview Tab) */}
                {activeTab === 'overview' && topEmployees.length > 0 && (
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>Top Performers by Hours</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {topEmployees.map((emp, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between p-3 rounded-lg border"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">
                                                {index + 1}
                                            </div>
                                            <div>
                                                <div className="font-medium">
                                                    {emp.employee_name}
                                                </div>
                                                <div className="text-sm text-muted-foreground">
                                                    {emp.position}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <div className="font-bold text-blue-600">
                                                {emp.total_hours}h
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {emp.total_days} days • {emp.attendance_rate}% present
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Status Distribution Comparison (Compare Tab) */}
                {activeTab === 'compare' && statusDistribution && (
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>Status Distribution Comparison</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {Object.keys(statusDistribution.period1).map((status) => (
                                    <div key={status}>
                                        <div className="flex justify-between text-sm mb-2">
                                            <span className="font-medium">{status}</span>
                                            <span className="text-muted-foreground">
                                                Period 1: {statusDistribution.period1[status]} |
                                                Period 2: {statusDistribution.period2[status]}
                                            </span>
                                        </div>
                                        <div className="flex gap-2 h-2">
                                            <div
                                                className="bg-blue-500 rounded"
                                                style={{
                                                    width: `${
                                                        (statusDistribution.period1[status] /
                                                            Math.max(
                                                                ...Object.values(statusDistribution.period1),
                                                                ...Object.values(statusDistribution.period2)
                                                            )) *
                                                        100
                                                    }%`,
                                                }}
                                            />
                                            <div
                                                className="bg-gray-400 rounded"
                                                style={{
                                                    width: `${
                                                        (statusDistribution.period2[status] /
                                                            Math.max(
                                                                ...Object.values(statusDistribution.period1),
                                                                ...Object.values(statusDistribution.period2)
                                                            )) *
                                                        100
                                                    }%`,
                                                }}
                                            />
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
