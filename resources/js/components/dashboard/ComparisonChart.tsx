import {
    ResponsiveContainer,
    LineChart,
    BarChart,
    AreaChart,
    Line,
    Bar,
    Area,
    XAxis,
    YAxis,
    Tooltip,
    CartesianGrid,
    Legend,
} from 'recharts';

type ChartDataPoint = {
    label: string;
    value: number;
};

type Props = {
    period1Data?: ChartDataPoint[];
    period1Label?: string;
    period2Data?: ChartDataPoint[];
    period2Label?: string;
    type?: 'line' | 'bar' | 'area';
};

export default function ComparisonChart({
    period1Data = [],
    period1Label = 'Period 1',
    period2Data = [],
    period2Label = 'Period 2',
    type = 'line',
}: Props) {
    // Check if data exists
    if (!period1Data.length && !period2Data.length) {
        return (
            <div className="flex items-center justify-center h-[300px] text-muted-foreground">
                No data available for comparison
            </div>
        );
    }

    // Merge data for comparison - align by index
    const maxLength = Math.max(period1Data.length, period2Data.length);
    const mergedData = Array.from({ length: maxLength }, (_, index) => ({
        label: period1Data[index]?.label || period2Data[index]?.label || `Point ${index + 1}`,
        period1: period1Data[index]?.value || 0,
        period2: period2Data[index]?.value || 0,
    }));

    const chartProps = {
        data: mergedData,
        children: (
            <>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="label" />
                <YAxis allowDecimals={false} />
                <Tooltip />
                <Legend />
            </>
        ),
    };

    return (
        <div className="space-y-2">
            {/* Legend Labels */}
            <div className="flex gap-4 text-xs text-muted-foreground justify-center">
                <div className="flex items-center gap-2">
                    <div className="w-3 h-3 rounded-full bg-blue-500" />
                    <span>{period1Label}</span>
                </div>
                <div className="flex items-center gap-2">
                    <div className="w-3 h-3 rounded-full bg-slate-400" />
                    <span>{period2Label}</span>
                </div>
            </div>

            <ResponsiveContainer width="100%" height={300}>
                {type === 'bar' && (
                    <BarChart {...chartProps}>
                        {chartProps.children}
                        <Bar dataKey="period1" fill="#3b82f6" name={period1Label} />
                        <Bar dataKey="period2" fill="#94a3b8" name={period2Label} />
                    </BarChart>
                )}

                {type === 'area' && (
                    <AreaChart {...chartProps}>
                        {chartProps.children}
                        <Area
                            type="monotone"
                            dataKey="period1"
                            fill="#3b82f6"
                            stroke="#3b82f6"
                            name={period1Label}
                            fillOpacity={0.6}
                        />
                        <Area
                            type="monotone"
                            dataKey="period2"
                            fill="#94a3b8"
                            stroke="#94a3b8"
                            name={period2Label}
                            fillOpacity={0.6}
                        />
                    </AreaChart>
                )}

                {type === 'line' && (
                    <LineChart {...chartProps}>
                        {chartProps.children}
                        <Line
                            type="monotone"
                            dataKey="period1"
                            stroke="#3b82f6"
                            strokeWidth={2}
                            name={period1Label}
                        />
                        <Line
                            type="monotone"
                            dataKey="period2"
                            stroke="#94a3b8"
                            strokeWidth={2}
                            name={period2Label}
                        />
                    </LineChart>
                )}
            </ResponsiveContainer>
        </div>
    );
}
