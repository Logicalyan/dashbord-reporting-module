import ChartSkeleton from '@/components/dashboard/ChartSkeleton';
import CompareCard from '@/components/dashboard/CompareCard';
import CompareFilters from '@/components/dashboard/CompareFilters';
import ComparisonChart from '@/components/dashboard/ComparisonChart';


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

type Props = {
    compareState: {
        period1_from: string;
        period1_to: string;
        period2_from: string;
        period2_to: string;
    };
    setCompareState: (state: any) => void;
    onApply: () => void;
    comparisons?: Record<string, ComparisonData> | undefined;
    comparisonCharts?: Record<
        string,
        {
            period1: {
                data: { label: string; value: number }[];
                label: string;
            };
            period2: {
                data: { label: string; value: number }[];
                label: string;
            };
        } | undefined
    >;
    kpiMetrics: Array<{ key: string; label: string; accent?: string }>;
    chartConfigs: Record<string, { label: string; type?: 'line' | 'bar' | 'area' }>;
    comparisonPresets?: Array<{ value: string; label: string }>;
    onPresetChange?: (preset: string) => void;
};

export default function DashboardCompare({
    compareState,
    setCompareState,
    onApply,
    comparisons,
    comparisonCharts,
    kpiMetrics,
    chartConfigs,
    comparisonPresets,
    onPresetChange,
}: Props) {
    const hasFilters =
        compareState.period1_from &&
        compareState.period1_to &&
        compareState.period2_from &&
        compareState.period2_to;

    return (
        <>
            {/* COMPARE FILTERS */}
            <CompareFilters
                compareState={compareState}
                setCompareState={setCompareState}
                onApply={onApply}
                comparisonPresets={comparisonPresets}
                onPresetChange={onPresetChange}
            />

            {/* Always show content if filters exist (including defaults) */}
            {hasFilters && (
                <>
                    {/* COMPARISON CARDS */}
                    <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                        {!comparisons ? (
                            <>
                                {kpiMetrics.map((metric) => (
                                    <div
                                        key={metric.key}
                                        className="rounded-xl border bg-white p-6 animate-pulse"
                                    >
                                        <div className="h-4 bg-gray-200 rounded w-2/3 mb-4" />
                                        <div className="h-8 bg-gray-200 rounded w-1/2" />
                                    </div>
                                ))}
                            </>
                        ) : (
                            kpiMetrics.map((metric) => {
                                const data = comparisons[metric.key];
                                return (
                                    <CompareCard
                                        key={metric.key}
                                        title={metric.label}
                                        period1={data?.period1}
                                        period2={data?.period2}
                                        difference={data?.difference}
                                        growth={data?.growth}
                                        accent={metric.accent}
                                    />
                                );
                            })
                        )}
                    </div>

                    {/* COMPARISON CHARTS */}
                    {comparisonCharts &&
                        Object.entries(chartConfigs).map(([key, config]) => {
                            const chartData = comparisonCharts[key];

                            return (
                                <div key={key} className="bg-white rounded-xl shadow p-6">
                                    <h2 className="text-lg font-semibold mb-4">
                                        {config.label} - Comparison
                                    </h2>

                                    {!chartData ? (
                                        <ChartSkeleton />
                                    ) : (
                                        <ComparisonChart
                                            period1Data={chartData.period1?.data}
                                            period1Label={chartData.period1?.label}
                                            period2Data={chartData.period2?.data}
                                            period2Label={chartData.period2?.label}
                                            type={config.type || 'line'}
                                        />
                                    )}
                                </div>
                            );
                        })}
                </>
            )}
        </>
    );
}
