/* eslint-disable react-hooks/set-state-in-effect */
import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import DashboardCompare from '@/components/dashboard/DashboardCompare';
import DashboardFilters from '@/components/dashboard/DashboardFilters';
import DashboardOverview from '@/components/dashboard/DashboardOverview';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
// import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type KpiMetric = {
    key: string;
    label: string;
    accent?: string;
};

type ChartConfig = {
    label: string;
    type?: 'line' | 'bar' | 'area';
};

type ChartData = {
    label: string;
    value: number;
}[];

type ComparisonData = {
    period1: {
        value: number;
        from: string;
        to: string;
    };
    period2: {
        value: number;
        from: string;
        to: string;
    };
    difference: number;
    growth: number;
};

type DashboardProps = {
    activeTab: string;
    kpiMetrics: KpiMetric[];
    chartConfigs: Record<string, ChartConfig>;
    breadcrumbs?: BreadcrumbItem[];
    routeUrl: string;

    // Overview tab
    filters?: {
        from?: string;
        to?: string;
    };
    summary?: Record<string, number>;
    growth?: number;
    charts?: Record<string, ChartData | undefined>;

    // Compare tab
    compareFilters?: {
        period1: {
            from?: string;
            to?: string;
        };
        period2: {
            from?: string;
            to?: string;
        };
    };
    comparisons?: Record<string, ComparisonData> | undefined;
    comparisonCharts?: Record<
        string,
        {
            period1: {
                data: ChartData;
                label: string;
            };
            period2: {
                data: ChartData;
                label: string;
            };
        } | undefined
    >;
    filterPresets?: Array<{ value: string; label: string }>;
    comparisonPresets?: Array<{ value: string; label: string }>;
    children?: React.ReactNode;
};

export default function GenericDashboard({
    activeTab,
    kpiMetrics,
    chartConfigs,
    // breadcrumbs = [],
    routeUrl,
    filters,
    summary,
    growth,
    charts,
    compareFilters,
    comparisons,
    comparisonCharts,
    filterPresets = [],
    comparisonPresets = [],
    children,
}: DashboardProps) {
    // Initialize state with server-provided filters (which include defaults)
    const [overviewFilters, setOverviewFilters] = useState({
        from: filters?.from || '',
        to: filters?.to || '',
    });

    const [compareState, setCompareState] = useState({
        period1_from: compareFilters?.period1.from || '',
        period1_to: compareFilters?.period1.to || '',
        period2_from: compareFilters?.period2.from || '',
        period2_to: compareFilters?.period2.to || '',
    });

    // Update state when server data changes (important for defaults)
    useEffect(() => {
        if (filters) {
            setOverviewFilters({
                from: filters.from || '',
                to: filters.to || '',
            });
        }
    }, [filters]);

    useEffect(() => {
        if (compareFilters) {
            setCompareState({
                period1_from: compareFilters.period1.from || '',
                period1_to: compareFilters.period1.to || '',
                period2_from: compareFilters.period2.from || '',
                period2_to: compareFilters.period2.to || '',
            });
        }
    }, [compareFilters]);

    const applyOverviewFilters = () => {
        router.get(
            routeUrl,
            { ...overviewFilters, tab: 'overview' },
            { preserveState: true }
        );
    };

    const applyCompareFilters = () => {
        router.get(
            routeUrl,
            { ...compareState, tab: 'compare' },
            { preserveState: true }
        );
    };

    const handleTabChange = (value: string) => {
        if (value === 'overview') {
            router.get(
                routeUrl,
                { ...overviewFilters, tab: value },
                { preserveState: true }
            );
        } else {
            router.get(
                routeUrl,
                { ...compareState, tab: value },
                { preserveState: true }
            );
        }
    };

    const handlePresetChange = (preset: string) => {
        router.get(
            routeUrl,
            { preset, tab: 'overview' },
            { preserveState: true }
        );
    };

    const handleComparisonPresetChange = (preset: string) => {
        router.get(
            routeUrl,
            { compare_preset: preset, tab: 'compare' },
            { preserveState: true }
        );
    };

    return (


             <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            {/* TABS */}
            <Tabs value={activeTab} onValueChange={handleTabChange}>
                <TabsList className="grid w-full max-w-md grid-cols-2">
                    <TabsTrigger value="overview">Overview</TabsTrigger>
                    <TabsTrigger value="compare">Compare</TabsTrigger>
                </TabsList>

                <TabsContent value="overview" className="space-y-4 mt-4">
                    {/* OVERVIEW FILTERS */}
                    <DashboardFilters
                        filterState={overviewFilters}
                        setFilterState={setOverviewFilters}
                        onApply={applyOverviewFilters}
                        presets={filterPresets}
                        onPresetChange={handlePresetChange}
                    />

                    <DashboardOverview
                        summary={summary}
                        growth={growth}
                        charts={charts}
                        kpiMetrics={kpiMetrics}
                        chartConfigs={chartConfigs}
                    />

                    {/* Custom Widgets for Overview */}
                    {children}
                </TabsContent>

                <TabsContent value="compare" className="space-y-4 mt-4">
                    <DashboardCompare
                        compareState={compareState}
                        setCompareState={setCompareState}
                        onApply={applyCompareFilters}
                        comparisons={comparisons}
                        comparisonCharts={comparisonCharts}
                        kpiMetrics={kpiMetrics}
                        chartConfigs={chartConfigs}
                        comparisonPresets={comparisonPresets}
                        onPresetChange={handleComparisonPresetChange}
                    />

                    {/* Custom Widgets for Compare */}
                    {children}
                </TabsContent>
            </Tabs>
        </div>

    );
}
