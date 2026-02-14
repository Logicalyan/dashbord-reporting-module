import { Head } from '@inertiajs/react';
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
    { title: 'Attendance', href: '/attendance' },
];

type Employee = {
    id: number;
    name: string;
    position: string;
    email: string;
};

type Props = {
    employees: Employee[];
};

export default function AttendanceManagement({ employees }: Props) {
    const [activeView, setActiveView] = useState<'table' | 'report'>('table');

    const { exportReport, isLoading, exportSuccess } = useReportExport({
        endpoint: '/api/reports/attendance/export',
        defaultFilename: 'attendance-report.xlsx',
    });

    // ==========================================
    // DATATABLE CONFIGURATION
    // ==========================================
    const dataTableColumns: ColumnDef[] = [
        {
            data: 'employee_name',
            title: 'Employee',
            searchable: true,
            render: (data, type, row) => {
                if (type === 'display') {
                    return `
                        <div>
                            <div class="font-medium">${data}</div>
                            <div class="text-xs text-gray-500">${row.employee_position}</div>
                        </div>
                    `;
                }
                return data;
            },
        },
        {
            data: 'date',
            title: 'Date',
            render: (data, type, row) => {
                if (type === 'display') {
                    return `
                        <div>
                            <div class="font-medium">${data}</div>
                            <div class="text-xs text-gray-500">${row.day_of_week}</div>
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
                const colors: Record<string, string> = {
                    Present: 'bg-green-100 text-green-800',
                    Absent: 'bg-red-100 text-red-800',
                    Late: 'bg-yellow-100 text-yellow-800',
                    Remote: 'bg-blue-100 text-blue-800',
                };
                const color = colors[data] || 'bg-gray-100 text-gray-800';
                return `<span class="px-2 py-1 rounded-full text-xs font-medium ${color}">${data}</span>`;
            },
        },
        {
            data: 'check_in',
            title: 'Check In',
            className: 'text-center',
        },
        {
            data: 'check_out',
            title: 'Check Out',
            className: 'text-center',
        },
        {
            data: 'hours',
            title: 'Hours',
            className: 'text-right',
            render: (data) => {
                return `<span class="font-medium">${parseFloat(data).toFixed(2)}</span>`;
            },
        },
        {
            data: 'overtime',
            title: 'Overtime',
            className: 'text-right',
            render: (data) => {
                if (parseFloat(data) > 0) {
                    return `<span class="font-medium text-orange-600">${parseFloat(data).toFixed(2)}</span>`;
                }
                return `<span class="text-gray-400">-</span>`;
            },
        },
        {
            data: 'total_hours',
            title: 'Total',
            className: 'text-right',
            render: (data) => {
                return `<span class="font-bold text-blue-600">${parseFloat(data).toFixed(2)}</span>`;
            },
        },
    ];

    const dataTableFilters: FilterField[] = [
        {
            name: 'start_date',
            label: 'Start Date',
            type: 'date',
        },
        {
            name: 'end_date',
            label: 'End Date',
            type: 'date',
        },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            options: [
                { value: 'Present', label: 'Present' },
                { value: 'Absent', label: 'Absent' },
                { value: 'Late', label: 'Late' },
                { value: 'Remote', label: 'Remote' },
            ],
            placeholder: 'All Statuses',
        },
        {
            name: 'employee_id',
            label: 'Employee',
            type: 'select',
            options: employees.map((emp) => ({
                value: emp.id.toString(),
                label: `${emp.name} (${emp.position})`,
            })),
            placeholder: 'All Employees',
        },
        {
            name: 'min_hours',
            label: 'Min Hours',
            type: 'number',
            placeholder: 'e.g., 4',
        },
        {
            name: 'max_hours',
            label: 'Max Hours',
            type: 'number',
            placeholder: 'e.g., 12',
        },
    ];

    // ==========================================
    // REPORT GENERATOR CONFIGURATION
    // ==========================================
    const reportFilterFields: FilterField[] = [
        {
            name: 'start_date',
            label: 'Start Date',
            type: 'date',
        },
        {
            name: 'end_date',
            label: 'End Date',
            type: 'date',
        },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            options: [
                { value: 'Present', label: 'Present' },
                { value: 'Absent', label: 'Absent' },
                { value: 'Late', label: 'Late' },
                { value: 'Remote', label: 'Remote' },
            ],
            placeholder: 'All Statuses',
        },
        {
            name: 'employee_id',
            label: 'Employee',
            type: 'select',
            options: employees.map((emp) => ({
                value: emp.id.toString(),
                label: `${emp.name} (${emp.position})`,
            })),
            placeholder: 'All Employees',
        },
        {
            name: 'employee_name',
            label: 'Search Employee',
            type: 'text',
            placeholder: 'Type to search...',
        },
        {
            name: 'active_employees_only',
            label: 'Active Employees Only',
            type: 'select',
            options: [
                { value: '1', label: 'Yes' },
                { value: '0', label: 'No' },
            ],
            placeholder: 'All',
        },
        {
            name: 'min_hours',
            label: 'Min Hours',
            type: 'number',
            placeholder: 'e.g., 4',
        },
        {
            name: 'max_hours',
            label: 'Max Hours',
            type: 'number',
            placeholder: 'e.g., 12',
        },
        {
            name: 'overtime_only',
            label: 'Overtime Only',
            type: 'select',
            options: [
                { value: '1', label: 'Yes' },
                { value: '0', label: 'No' },
            ],
            placeholder: 'All',
        },
        {
            name: 'sort_by',
            label: 'Sort By',
            type: 'select',
            options: [
                { value: 'date', label: 'Date' },
                { value: 'hours', label: 'Hours' },
                { value: 'overtime', label: 'Overtime' },
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
        const url = `/api/reports/attendance/export?${queryParams}`;

        try {
            const response = await fetch(url);
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `attendance-quick-export-${new Date().toISOString().split('T')[0]}.xlsx`;
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
        console.log('Clicked row:', row);
        // You can navigate to detail page or open a modal
        // router.visit(`/attendance/${row.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Attendance Management" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold">Attendance Management</h1>
                    <p className="text-muted-foreground mt-2">
                        View attendance records or generate comprehensive reports.
                    </p>
                </div>

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
                            title="Attendance Records"
                            columns={dataTableColumns}
                            ajaxUrl="/api/attendance/datatable"
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
                                            <strong>Summary Sheet:</strong> Visual bar chart showing attendance distribution by status (Present, Absent, Late, Remote)
                                        </div>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <FileSpreadsheet className="h-4 w-4 text-blue-600 mt-0.5" />
                                        <div>
                                            <strong>Detail Sheet:</strong> Complete attendance records including employee name, email, position, date, status, check-in/out times, hours worked, and overtime
                                        </div>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <FileSpreadsheet className="h-4 w-4 text-blue-600 mt-0.5" />
                                        <div>
                                            <strong>Statistics Section:</strong> Automated calculations for total records, unique days, unique employees, total hours, average hours, overtime totals, and status breakdown
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
                                sort_by: 'date',
                                sort_dir: 'desc',
                                active_employees_only: '1',
                            }}
                        />
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
