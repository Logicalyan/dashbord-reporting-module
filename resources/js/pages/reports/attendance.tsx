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
    { title: 'Attendance Report', href: '/reports/attendance' },
];

type Employee = {
    id: number;
    name: string;
    label?: string;
};

type Props = {
    employees: Employee[];
    positions?: string[];
    datePresets?: Record<string, string>;
};

export default function AttendanceReport({ employees }: Props) {
    const { exportReport, isLoading, exportSuccess } = useReportExport({
        endpoint: '/api/reports/attendance/export',
        defaultFilename: 'attendance-report.xlsx',
    });

    const filterFields: FilterField[] = [
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
                label: emp.label || emp.name,
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Attendance Report" />

            <div className="space-y-6 p-6">
                <div>
                    <h1 className="text-3xl font-bold">Attendance Report</h1>
                    <p className="text-muted-foreground mt-2">
                        Generate comprehensive attendance reports with detailed statistics and charts.
                    </p>
                </div>

                {exportSuccess && (
                    <Alert className="border-green-200 bg-green-50">
                        <CheckCircle className="h-4 w-4 text-green-600" />
                        <AlertDescription className="text-green-800">
                            Report exported successfully! Check your downloads folder.
                        </AlertDescription>
                    </Alert>
                )}

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
                                <strong>Summary Sheet:</strong> Bar chart showing attendance distribution by status
                            </li>
                            <li className="flex items-center gap-2">
                                <FileSpreadsheet className="h-4 w-4 text-blue-600" />
                                <strong>Detail Sheet:</strong> Complete attendance records with employee info, dates, hours, and overtime
                            </li>
                            <li className="flex items-center gap-2">
                                <FileSpreadsheet className="h-4 w-4 text-blue-600" />
                                <strong>Statistics:</strong> Total records, hours worked, overtime, and attendance breakdown
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <ReportFilters
                    fields={filterFields}
                    onExport={exportReport}
                    isLoading={isLoading}
                    defaultFilters={{
                        sort_by: 'date',
                        sort_dir: 'desc',
                        active_employees_only: '1', // Default to active employees only
                    }}
                />
            </div>
        </AppLayout>
    );
}
