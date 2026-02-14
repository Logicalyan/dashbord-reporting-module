type Props = {
    title: string;
    value: number;
    accent?: string;
};

export default function KpiCard({
    title,
    value,
    accent = 'text-foreground',
}: Props) {
    return (
        <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
            <p className="text-sm text-muted-foreground">
                {title}
            </p>

            <p className={`mt-2 text-3xl font-bold ${accent}`}>
                {value}
            </p>
        </div>
    );
}
