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
} from 'recharts';

type Props = {
    data: { label: string; value: number }[];
    type?: 'line' | 'bar' | 'area';
};

export default function DynamicChart({ data, type = 'line' }: Props) {
    const chartProps = {
        data,
        children: (
            <>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="label" />
                <YAxis allowDecimals={false} />
                <Tooltip />
            </>
        ),
    };

    return (
        <ResponsiveContainer width="100%" height={300}>
            {type === 'bar' && (
                <BarChart {...chartProps}>
                    {chartProps.children}
                    <Bar dataKey="value" fill="#8884d8" />
                </BarChart>
            )}

            {type === 'area' && (
                <AreaChart {...chartProps}>
                    {chartProps.children}
                    <Area
                        type="monotone"
                        dataKey="value"
                        fill="#8884d8"
                        stroke="#8884d8"
                    />
                </AreaChart>
            )}

            {type === 'line' && (
                <LineChart {...chartProps}>
                    {chartProps.children}
                    <Line type="monotone" dataKey="value" strokeWidth={2} />
                </LineChart>
            )}
        </ResponsiveContainer>
    );
}
