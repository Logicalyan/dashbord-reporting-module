import { Head } from '@inertiajs/react';
import { CheckCircle, FileSpreadsheet, Info } from 'lucide-react';
import type { FilterField } from '@/components/reports/ReportFilters';
import ReportFilters from '@/components/reports/ReportFilters';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useReportExport } from '@/hooks/useReportExport';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: '/reports' },
    { title: 'Company Report', href: '/reports/company' },
];

type Props = {
    statuses: string[];
    stats?: {
        total_companies: number;
        active_companies: number;
        inactive_companies: number;
    };
    datePresets?: Record<string, string>;
};

export default function CompanyReport({ statuses, stats }: Props) {
    const { exportReport, isLoading, exportSuccess } = useReportExport({
        endpoint: '/api/reports/company/export',
        defaultFilename: 'company-report.xlsx',
        onError: (error) => {
            alert('Failed to export report. Please try again.');
        },
    });

    const filterFields: FilterField[] = [
        {
            name: 'start_date',
            label: 'Joined From',
            type: 'date',
        },
        {
            name: 'end_date',
            label: 'Joined To',
            type: 'date',
        },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            options: statuses.map((status) => ({
                value: status,
                label: status.charAt(0).toUpperCase() + status.slice(1),
            })),
            placeholder: 'All Statuses',
        },
        {
            name: 'sort_by',
            label: 'Sort By',
            type: 'select',
            options: [
                { value: 'joined_at', label: 'Joined Date' },
                { value: 'name', label: 'Company Name' },
                { value: 'status', label: 'Status' },
                { value: 'email', label: 'Email' },
            ],
        },
        {
            name: 'sort_dir',
            label: 'Sort Direction',
            type: 'select',
            options: [
                { value: 'asc', label: 'Ascending' },
                { value: 'desc', label: 'Descending' },
            ],
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company Report" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold">Company Report</h1>
                    <p className="text-muted-foreground mt-2">
                        Generate comprehensive company reports with detailed statistics.
                    </p>
                </div>

                {/* Success Message */}
                {exportSuccess && (
                    <Alert className="border-green-200 bg-green-50">
                        <CheckCircle className="h-4 w-4 text-green-600" />
                        <AlertDescription className="text-green-800">
                            Report exported successfully! Check your downloads folder.
                        </AlertDescription>
                    </Alert>
                )}

                {/* Stats Card (Optional) */}
                {stats && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Total Companies
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.total_companies}</div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Active Companies
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">
                                    {stats.active_companies}
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Inactive Companies
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-red-600">
                                    {stats.inactive_companies}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Info Card */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Info className="h-5 w-5" />
                            Report Information
                        </CardTitle>
                        <CardDescription>
                            This report includes the following data:
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul className="space-y-2 text-sm">
                            <li className="flex items-center gap-2">
                                <FileSpreadsheet className="h-4 w-4 text-blue-600" />
                                <strong>Summary Sheet:</strong> Pie chart showing active vs inactive companies
                            </li>
                            <li className="flex items-center gap-2">
                                <FileSpreadsheet className="h-4 w-4 text-blue-600" />
                                <strong>Detail Sheet:</strong> Complete company records with name, email, status, and join date
                            </li>
                            <li className="flex items-center gap-2">
                                <FileSpreadsheet className="h-4 w-4 text-blue-600" />
                                <strong>Statistics:</strong> Total companies, active/inactive count, and membership duration
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                {/* Filters */}
                <ReportFilters
                    fields={filterFields}
                    onExport={exportReport}
                    isLoading={isLoading}
                    defaultFilters={{
                        sort_by: 'joined_at',
                        sort_dir: 'desc',
                    }}
                />
            </div>
        </AppLayout>
    );
}
