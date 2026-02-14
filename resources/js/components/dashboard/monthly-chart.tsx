import {
    ResponsiveContainer,
    LineChart,
    Line,
    XAxis,
    YAxis,
    Tooltip,
    CartesianGrid,
} from 'recharts'

export default function MonthlyChart({ data } : { data: { label: string; value: number }[] }) {
    return (
        <ResponsiveContainer width="100%" height={300}>
            <LineChart data={data}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="label" />
                <YAxis allowDecimals={false} />
                <Tooltip />
                <Line
                    type="monotone"
                    dataKey="value"
                    strokeWidth={2}
                />
            </LineChart>
        </ResponsiveContainer>
    )
}
