import { Download, Filter, RefreshCw, X } from 'lucide-react';
import { useState, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import BaseDataTable from './BaseDataTable';
import type { ColumnDef, DataTableConfig } from './BaseDataTable';

export type FilterField = {
    name: string;
    label: string;
    type: 'text' | 'select' | 'date' | 'number';
    options?: Array<{ value: string; label: string }>;
    placeholder?: string;
};

type Props = {
    title?: string;
    columns: ColumnDef[];
    ajaxUrl?: string;
    serverSide?: boolean;
    data?: any[];
    filters?: FilterField[];
    onExport?: (filters: Record<string, any>) => void;
    onRowClick?: (row: any) => void;
    pageLength?: number;
    defaultFilters?: Record<string, any>;
    showFilters?: boolean;
    exportable?: boolean;
};

export default function EnhancedDataTable({
    title,
    columns,
    ajaxUrl,
    serverSide = false,
    data = [],
    filters = [],
    onExport,
    onRowClick,
    pageLength = 10,
    defaultFilters = {},
    showFilters = true,
    exportable = true,
}: Props) {
    const [filterValues, setFilterValues] = useState<Record<string, any>>(defaultFilters);
    const [isFilterVisible, setIsFilterVisible] = useState(false);
    const [tableKey, setTableKey] = useState(0);

    const handleFilterChange = (name: string, value: any) => {
        setFilterValues((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleApplyFilters = useCallback(() => {
        // Force table reload with new filters
        setTableKey((prev) => prev + 1);
    }, []);

    const handleClearFilters = useCallback(() => {
        setFilterValues(defaultFilters);
        setTableKey((prev) => prev + 1);
    }, [defaultFilters]);

    const handleExport = () => {
        if (onExport) {
            const cleanFilters = Object.fromEntries(
                Object.entries(filterValues).filter(
                    ([_, value]) => value !== '' && value !== null && value !== undefined
                )
            );
            onExport(cleanFilters);
        }
    };

    const tableConfig: DataTableConfig = {
        columns,
        pageLength,
        responsive: true,
        processing: serverSide,
        serverSide,
        order: [[0, 'desc']],
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'All'],
        ],
        language: {
            processing: 'Loading data...',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous',
            },
            emptyTable: 'No data available',
        },
    };

    if (serverSide && ajaxUrl) {
        tableConfig.ajax = {
            url: ajaxUrl,
            type: 'GET',
            data: (d: any) => {
                // Merge DataTables params with filter values
                return {
                    ...d,
                    ...filterValues,
                };
            },
        };
    }

    const renderFilter = (filter: FilterField) => {
        const value = filterValues[filter.name] || '';

        switch (filter.type) {
            case 'select':
                return (
                    <div key={filter.name} className="space-y-2">
                        <Label>{filter.label}</Label>
                        <Select
                            value={value}
                            onValueChange={(val) => handleFilterChange(filter.name, val)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder={filter.placeholder || `Select ${filter.label}`} />
                            </SelectTrigger>
                            <SelectContent>
                                {filter.options?.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                );

            case 'date':
                return (
                    <div key={filter.name} className="space-y-2">
                        <Label htmlFor={filter.name}>{filter.label}</Label>
                        <Input
                            id={filter.name}
                            type="date"
                            value={value}
                            onChange={(e) => handleFilterChange(filter.name, e.target.value)}
                        />
                    </div>
                );

            case 'number':
                return (
                    <div key={filter.name} className="space-y-2">
                        <Label htmlFor={filter.name}>{filter.label}</Label>
                        <Input
                            id={filter.name}
                            type="number"
                            value={value}
                            onChange={(e) => handleFilterChange(filter.name, e.target.value)}
                            placeholder={filter.placeholder}
                        />
                    </div>
                );

            default:
                return (
                    <div key={filter.name} className="space-y-2">
                        <Label htmlFor={filter.name}>{filter.label}</Label>
                        <Input
                            id={filter.name}
                            type="text"
                            value={value}
                            onChange={(e) => handleFilterChange(filter.name, e.target.value)}
                            placeholder={filter.placeholder}
                        />
                    </div>
                );
        }
    };

    return (
        <div className="space-y-4">
            {/* Header */}
            {(title || showFilters || exportable) && (
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            {title && <CardTitle>{title}</CardTitle>}
                            <div className="flex gap-2">
                                {showFilters && filters.length > 0 && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setIsFilterVisible(!isFilterVisible)}
                                    >
                                        <Filter className="h-4 w-4 mr-2" />
                                        {isFilterVisible ? 'Hide' : 'Show'} Filters
                                    </Button>
                                )}
                                {exportable && onExport && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={handleExport}
                                    >
                                        <Download className="h-4 w-4 mr-2" />
                                        Export
                                    </Button>
                                )}
                            </div>
                        </div>
                    </CardHeader>

                    {/* Filters */}
                    {isFilterVisible && filters.length > 0 && (
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {filters.map((filter) => renderFilter(filter))}
                            </div>

                            <div className="flex gap-3 justify-end pt-4 border-t">
                                <Button variant="outline" onClick={handleClearFilters}>
                                    <X className="h-4 w-4 mr-2" />
                                    Clear
                                </Button>
                                <Button onClick={handleApplyFilters}>
                                    <RefreshCw className="h-4 w-4 mr-2" />
                                    Apply Filters
                                </Button>
                            </div>
                        </CardContent>
                    )}
                </Card>
            )}

            {/* DataTable */}
            <Card>
                <CardContent className="pt-6">
                    <BaseDataTable
                        key={tableKey}
                        config={tableConfig}
                        data={data}
                        onRowClick={onRowClick}
                    />
                </CardContent>
            </Card>
        </div>
    );
}
