import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type ComparisonPreset = {
    value: string;
    label: string;
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
    comparisonPresets?: ComparisonPreset[];
    onPresetChange?: (preset: string) => void;
};

export default function CompareFilters({
    compareState,
    setCompareState,
    onApply,
    comparisonPresets = [],
    onPresetChange,
}: Props) {
    return (
        <div className="space-y-4">
            {/* Comparison Preset Selector */}
            {comparisonPresets.length > 0 && onPresetChange && (
                <Select onValueChange={onPresetChange}>
                    <SelectTrigger className="w-full md:w-[300px]">
                        <SelectValue placeholder="Quick Compare" />
                    </SelectTrigger>
                    <SelectContent>
                        {comparisonPresets.map((preset) => (
                            <SelectItem key={preset.value} value={preset.value}>
                                {preset.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}

            {/* Manual Period Inputs */}
            <div className="grid gap-4 md:grid-cols-2">
                {/* Period 1 */}
                <Card className="p-4">
                    <h3 className="font-semibold mb-3 text-sm text-muted-foreground">
                        Period 1
                    </h3>
                    <div className="space-y-3">
                        <div>
                            <Label htmlFor="period1_from" className="text-xs">
                                From
                            </Label>
                            <Input
                                id="period1_from"
                                type="date"
                                value={compareState.period1_from}
                                onChange={(e) =>
                                    setCompareState({
                                        ...compareState,
                                        period1_from: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div>
                            <Label htmlFor="period1_to" className="text-xs">
                                To
                            </Label>
                            <Input
                                id="period1_to"
                                type="date"
                                value={compareState.period1_to}
                                onChange={(e) =>
                                    setCompareState({
                                        ...compareState,
                                        period1_to: e.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>
                </Card>

                {/* Period 2 */}
                <Card className="p-4">
                    <h3 className="font-semibold mb-3 text-sm text-muted-foreground">
                        Period 2
                    </h3>
                    <div className="space-y-3">
                        <div>
                            <Label htmlFor="period2_from" className="text-xs">
                                From
                            </Label>
                            <Input
                                id="period2_from"
                                type="date"
                                value={compareState.period2_from}
                                onChange={(e) =>
                                    setCompareState({
                                        ...compareState,
                                        period2_from: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div>
                            <Label htmlFor="period2_to" className="text-xs">
                                To
                            </Label>
                            <Input
                                id="period2_to"
                                type="date"
                                value={compareState.period2_to}
                                onChange={(e) =>
                                    setCompareState({
                                        ...compareState,
                                        period2_to: e.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>
                </Card>

                {/* Apply Button - Full Width */}
                <div className="md:col-span-2">
                    <Button onClick={onApply} className="w-full">
                        Compare Periods
                    </Button>
                </div>
            </div>
        </div>
    );
}
