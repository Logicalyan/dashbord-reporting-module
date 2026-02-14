export default function ChartSkeleton() {
    return (
        <div className="animate-pulse space-y-4">
            <div className="h-4 bg-gray-200 rounded w-1/3" />

            <div className="h-64 bg-gray-100 rounded-lg flex items-end gap-2 p-4">
                {Array.from({ length: 12 }).map((_, i) => (
                    <div
                        key={i}
                        className="bg-gray-300 rounded w-full"
                        style={{ height: `${20 + i * 4}%` }}
                    />
                ))}
            </div>
        </div>
    )
}
