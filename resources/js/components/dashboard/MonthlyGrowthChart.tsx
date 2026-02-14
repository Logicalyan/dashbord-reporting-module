import {
    ResponsiveContainer,
    BarChart,
    Bar,
    XAxis,
    YAxis,
    Tooltip,
} from 'recharts';

type ChartData = {
    label: string;
    value: number;
};

type Props = {
    data: ChartData[];
};

export default function MonthlyGrowthChart({ data }: Props) {
    return (
        <div className="h-[260px] w-full">
            <ResponsiveContainer width="100%" height="100%">
                <BarChart data={data}>
                    <XAxis
                        dataKey="label"
                        tickLine={false}
                        axisLine={false}
                        fontSize={12}
                    />
                    <YAxis
                        tickLine={false}
                        axisLine={false}
                        fontSize={12}
                    />
                    <Tooltip
                        cursor={{ fill: 'transparent' }}
                        contentStyle={{
                            borderRadius: 8,
                            fontSize: 12,
                        }}
                    />
                    <Bar
                        dataKey="value"
                        radius={[6, 6, 0, 0]}
                        fill="hsl(var(--primary))"
                    />
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}
