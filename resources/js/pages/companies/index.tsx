import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { CheckCircle, FileSpreadsheet, Info, Table as TableIcon } from 'lucide-react';
import { useState } from 'react';
import type { ColumnDef } from '@/components/datatable/BaseDataTable';
import EnhancedDataTable from '@/components/datatable/EnhancedDataTable';
import type { FilterField } from '@/components/reports/ReportFilters';
import ReportFilters from '@/components/reports/ReportFilters';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useReportExport } from '@/hooks/useReportExport';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
    
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Companies', href: '/companies' },
];

type Props = {
    statuses: string[];
    stats?: {
        total_companies: number;
        active_companies: number;
        inactive_companies: number;
    };
};

export default function CompanyManagement({ statuses, stats }: Props) {
    const [activeView, setActiveView] = useState<'table' | 'report'>('table');

    const { exportReport, isLoading, exportSuccess } = useReportExport({
        endpoint: '/api/reports/company/export',
        defaultFilename: 'company-report.xlsx',
    });

    // ==========================================
    // DATATABLE CONFIGURATION
    // ==========================================
    const dataTableColumns: ColumnDef[] = [
        {
            data: 'name',
            title: 'Company Name',
            searchable: true,
            render: (data, type, row) => {
                if (type === 'display') {
                    return `
                        <div>
                            <div class="font-medium text-gray-900">${data}</div>
                            <div class="text-sm text-gray-500">${row.email}</div>
                        </div>
                    `;
                }
                return data;
            },
        },
        {
            data: 'status',
            title: 'Status',
            render: (data) => {
                const isActive = data === 'Active';
                const color = isActive
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-800';
                return `<span class="px-2 py-1 rounded-full text-xs font-medium ${color}">${data}</span>`;
            },
        },
        {
            data: 'joined_at',
            title: 'Joined Date',
            render: (data) => {
                return `<span class="font-medium">${data}</span>`;
            },
        },
        {
            data: 'days_since_joined',
            title: 'Membership',
            className: 'text-right',
            render: (data) => {
                const years = Math.floor(data / 365);
                const months = Math.floor((data % 365) / 30);

                if (years > 0) {
                    return `<span class="font-medium text-blue-600">${years}y ${months}m</span>`;
                } else if (months > 0) {
                    return `<span class="font-medium text-blue-600">${months} months</span>`;
                } else {
                    return `<span class="font-medium text-blue-600">${data} days</span>`;
                }
            },
        },
        {
            data: null,
            title: 'Actions',
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: (data, type, row) => {
                return `
                    <div class="flex gap-2 justify-center">
                        <button class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded"
                                data-action="view" data-id="${row.id}">
                            View
                        </button>
                        <button class="px-3 py-1 text-sm text-green-600 hover:text-green-800 hover:bg-green-50 rounded"
                                data-action="edit" data-id="${row.id}">
                            Edit
                        </button>
                    </div>
                `;
            },
        },
    ];

    const dataTableFilters: FilterField[] = [
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
    ];

    // ==========================================
    // REPORT GENERATOR CONFIGURATION
    // ==========================================
    const reportFilterFields: FilterField[] = [
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

    // ==========================================
    // HANDLERS
    // ==========================================
    const handleQuickExport = async (filters: Record<string, any>) => {
        const queryParams = new URLSearchParams(filters).toString();
        const url = `/api/reports/company/export?${queryParams}`;

        try {
            const response = await fetch(url);
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `company-quick-export-${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(downloadUrl);
        } catch (error) {
            console.error('Export error:', error);
            alert('Failed to export. Please try again.');
        }
    };

    const handleRowClick = (row: any) => {
        router.visit(`/companies/${row.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company Management" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold">Company Management</h1>
                    <p className="text-muted-foreground mt-2">
                        View company records or generate comprehensive reports.
                    </p>
                </div>

                {/* Stats Cards (Optional) */}
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

                {/* Success Alert */}
                {exportSuccess && (
                    <Alert className="border-green-200 bg-green-50">
                        <CheckCircle className="h-4 w-4 text-green-600" />
                        <AlertDescription className="text-green-800">
                            Report exported successfully! Check your downloads folder.
                        </AlertDescription>
                    </Alert>
                )}

                {/* Tabs */}
                <Tabs value={activeView} onValueChange={(v) => setActiveView(v as any)}>
                    <TabsList className="grid w-full max-w-md grid-cols-2">
                        <TabsTrigger value="table" className="flex items-center gap-2">
                            <TableIcon className="h-4 w-4" />
                            View Data
                        </TabsTrigger>
                        <TabsTrigger value="report" className="flex items-center gap-2">
                            <FileSpreadsheet className="h-4 w-4" />
                            Generate Report
                        </TabsTrigger>
                    </TabsList>

                    {/* TAB 1: DataTable View */}
                    <TabsContent value="table" className="space-y-4 mt-6">
                        <EnhancedDataTable
                            title="Company Records"
                            columns={dataTableColumns}
                            ajaxUrl="/api/companies/datatable"
                            serverSide={true}
                            filters={dataTableFilters}
                            onExport={handleQuickExport}
                            onRowClick={handleRowClick}
                            pageLength={25}
                            exportable={true}
                            showFilters={true}
                        />
                    </TabsContent>

                    {/* TAB 2: Report Generator */}
                    <TabsContent value="report" className="space-y-4 mt-6">
                        {/* Info Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Info className="h-5 w-5" />
                                    Report Information
                                </CardTitle>
                                <CardDescription>
                                    Generate a comprehensive Excel report with the following data:
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <ul className="space-y-2 text-sm">
                                    <li className="flex items-start gap-2">
                                        <FileSpreadsheet className="h-4 w-4 text-blue-600 mt-0.5" />
                                        <div>
                                            <strong>Summary Sheet:</strong> Pie chart showing company distribution by status (Active vs Inactive)
                                        </div>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <FileSpreadsheet className="h-4 w-4 text-blue-600 mt-0.5" />
                                        <div>
                                            <strong>Detail Sheet:</strong> Complete company records including name, email, status, join date, and membership duration
                                        </div>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <FileSpreadsheet className="h-4 w-4 text-blue-600 mt-0.5" />
                                        <div>
                                            <strong>Statistics Section:</strong> Automated calculations for total companies, active/inactive breakdown, and membership statistics
                                        </div>
                                    </li>
                                </ul>
                            </CardContent>
                        </Card>

                        {/* Report Filters */}
                        <ReportFilters
                            fields={reportFilterFields}
                            onExport={exportReport}
                            isLoading={isLoading}
                            defaultFilters={{
                                sort_by: 'joined_at',
                                sort_dir: 'desc',
                            }}
                        />
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
