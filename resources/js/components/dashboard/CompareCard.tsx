import { TrendingUp, TrendingDown, Minus, ArrowRight } from 'lucide-react';

type Props = {
    title: string;
    period1?: {
        value: number;
        from: string;
        to: string;
    };
    period2?: {
        value: number;
        from: string;
        to: string;
    };
    difference?: number;
    growth?: number;
    accent?: string;
};

export default function CompareCard({
    title,
    period1,
    period2,
    difference = 0,
    growth = 0,
    accent = 'text-foreground',
}: Props) {
    const getTrendIcon = () => {
        if (growth > 0) return <TrendingUp className="h-5 w-5 text-green-600" />;
        if (growth < 0) return <TrendingDown className="h-5 w-5 text-red-600" />;
        return <Minus className="h-5 w-5 text-gray-400" />;
    };

    const getGrowthColor = () => {
        if (growth > 0) return 'text-green-600';
        if (growth < 0) return 'text-red-600';
        return 'text-gray-600';
    };

    const getDifferenceColor = () => {
        if (difference > 0) return 'text-green-600';
        if (difference < 0) return 'text-red-600';
        return 'text-gray-600';
    };

    return (
        <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
            <p className="text-sm text-muted-foreground mb-4">{title}</p>

            <div className="space-y-4">
                {/* Period 1 */}
                <div>
                    <p className="text-xs text-muted-foreground mb-1">Period 1</p>
                    <p className={`text-2xl font-bold ${accent}`}>
                        {period1?.value.toLocaleString() || 0}
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">
                        {period1?.from} - {period1?.to}
                    </p>
                </div>

                {/* Arrow Divider */}
                <div className="flex items-center justify-center">
                    <ArrowRight className="h-5 w-5 text-muted-foreground" />
                </div>

                {/* Period 2 */}
                <div>
                    <p className="text-xs text-muted-foreground mb-1">Period 2</p>
                    <p className={`text-2xl font-bold ${accent}`}>
                        {period2?.value.toLocaleString() || 0}
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">
                        {period2?.from} - {period2?.to}
                    </p>
                </div>

                {/* Growth & Difference */}
                <div className="pt-3 border-t space-y-2">
                    <div className="flex items-center justify-between">
                        <span className="text-xs text-muted-foreground">Growth</span>
                        <div className="flex items-center gap-2">
                            {getTrendIcon()}
                            <span className={`text-sm font-semibold ${getGrowthColor()}`}>
                                {growth > 0 ? '+' : ''}
                                {growth}%
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center justify-between">
                        <span className="text-xs text-muted-foreground">Difference</span>
                        <span className={`text-sm font-semibold ${getDifferenceColor()}`}>
                            {difference > 0 ? '+' : ''}
                            {difference?.toLocaleString()}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
