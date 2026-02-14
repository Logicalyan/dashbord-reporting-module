import { Download, Filter, X } from 'lucide-react';
import { useState } from 'react';
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

export type FilterField = {
    name: string;
    label: string;
    type: 'text' | 'date' | 'select' | 'number';
    options?: Array<{ value: string; label: string }>;
    placeholder?: string;
};

type Props = {
    fields: FilterField[];
    onExport: (filters: Record<string, any>) => void;
    isLoading?: boolean;
    defaultFilters?: Record<string, any>;
};

export default function ReportFilters({
    fields,
    onExport,
    isLoading = false,
    defaultFilters = {},
}: Props) {
    const [filters, setFilters] = useState<Record<string, any>>(defaultFilters);
    const [showFilters, setShowFilters] = useState(false);

    const handleFilterChange = (name: string, value: any) => {
        setFilters((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleClearFilters = () => {
        setFilters({});
    };

    const handleExport = () => {
        // Remove empty filters
        const cleanFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
        );
        onExport(cleanFilters);
    };

    const renderField = (field: FilterField) => {
        const value = filters[field.name] || '';

        switch (field.type) {
            case 'select':
                return (
                    <div key={field.name} className="space-y-2">
                        <Label>{field.label}</Label>
                        <Select
                            value={value}
                            onValueChange={(val) => handleFilterChange(field.name, val)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder={field.placeholder || `Select ${field.label}`} />
                            </SelectTrigger>
                            <SelectContent>
                                {field.options?.map((option) => (
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
                    <div key={field.name} className="space-y-2">
                        <Label htmlFor={field.name}>{field.label}</Label>
                        <Input
                            id={field.name}
                            type="date"
                            value={value}
                            onChange={(e) => handleFilterChange(field.name, e.target.value)}
                        />
                    </div>
                );

            case 'number':
                return (
                    <div key={field.name} className="space-y-2">
                        <Label htmlFor={field.name}>{field.label}</Label>
                        <Input
                            id={field.name}
                            type="number"
                            value={value}
                            onChange={(e) => handleFilterChange(field.name, e.target.value)}
                            placeholder={field.placeholder}
                        />
                    </div>
                );

            default:
                return (
                    <div key={field.name} className="space-y-2">
                        <Label htmlFor={field.name}>{field.label}</Label>
                        <Input
                            id={field.name}
                            type="text"
                            value={value}
                            onChange={(e) => handleFilterChange(field.name, e.target.value)}
                            placeholder={field.placeholder}
                        />
                    </div>
                );
        }
    };

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <CardTitle className="flex items-center gap-2">
                        <Filter className="h-5 w-5" />
                        Report Filters
                    </CardTitle>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setShowFilters(!showFilters)}
                    >
                        {showFilters ? 'Hide' : 'Show'} Filters
                    </Button>
                </div>
            </CardHeader>

            {showFilters && (
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {fields.map((field) => renderField(field))}
                    </div>

                    <div className="flex gap-3 justify-end pt-4 border-t">
                        <Button
                            variant="outline"
                            onClick={handleClearFilters}
                            disabled={isLoading}
                        >
                            <X className="h-4 w-4 mr-2" />
                            Clear Filters
                        </Button>
                        <Button onClick={handleExport} disabled={isLoading}>
                            <Download className="h-4 w-4 mr-2" />
                            {isLoading ? 'Generating...' : 'Export Excel'}
                        </Button>
                    </div>
                </CardContent>
            )}
        </Card>
    );
}
