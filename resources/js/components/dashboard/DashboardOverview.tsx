import KpiCard from '@/components/dashboard/KpiCard';
import ChartSkeleton from '@/components/dashboard/ChartSkeleton';
import DynamicChart from '@/components/dashboard/DynamicChart';

type Props = {
    summary?: Record<string, number>;
    growth?: number;
    charts?: Record<string, { label: string; value: number }[] | undefined>;
    kpiMetrics: Array<{ key: string; label: string; accent?: string }>;
    chartConfigs: Record<string, { label: string; type?: 'line' | 'bar' | 'area' }>;
};

export default function DashboardOverview({
    summary,
    growth,
    charts,
    kpiMetrics,
    chartConfigs,
}: Props) {
    return (
        <>
            {/* GROWTH INDICATOR */}
            {growth !== undefined && growth !== 0 && (
                <div className="rounded-xl border bg-white p-4">
                    <p className="text-sm text-muted-foreground">Growth (MoM)</p>
                    <p
                        className={`text-2xl font-bold ${
                            growth > 0 ? 'text-green-600' : 'text-red-600'
                        }`}
                    >
                        {growth > 0 ? '+' : ''}
                        {growth}%
                    </p>
                </div>
            )}

            {/* KPI CARDS */}
            <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                {kpiMetrics.map((metric) => (
                    <KpiCard
                        key={metric.key}
                        title={metric.label}
                        value={summary?.[metric.key] || 0}
                        accent={metric.accent}
                    />
                ))}
            </div>

            {/* CHARTS */}
            {charts &&
                Object.entries(chartConfigs).map(([key, config]) => (
                    <div key={key} className="bg-white rounded-xl shadow p-6">
                        <h2 className="text-lg font-semibold mb-4">{config.label}</h2>

                        {!charts[key] ? (
                            <ChartSkeleton />
                        ) : (
                            <DynamicChart data={charts[key]!} type={config.type || 'line'} />
                        )}
                    </div>
                ))}
        </>
    );
}
