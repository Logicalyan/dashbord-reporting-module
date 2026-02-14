import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type FilterPreset = {
    value: string;
    label: string;
};

type Props = {
    filterState: {
        from: string;
        to: string;
    };
    setFilterState: (state: { from: string; to: string }) => void;
    onApply: () => void;
    presets?: FilterPreset[];
    onPresetChange?: (preset: string) => void;
};

export default function DashboardFilters({
    filterState,
    setFilterState,
    onApply,
    presets = [],
    onPresetChange,
}: Props) {
    return (
        <div className="flex flex-col gap-3 md:flex-row">
            {/* Preset Selector */}
            {presets.length > 0 && onPresetChange && (
                <Select onValueChange={onPresetChange}>
                    <SelectTrigger className="w-full md:w-[200px]">
                        <SelectValue placeholder="Quick Select" />
                    </SelectTrigger>
                    <SelectContent>
                        {presets.map((preset) => (
                            <SelectItem key={preset.value} value={preset.value}>
                                {preset.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}

            {/* Manual Date Inputs */}
            <Input
                type="date"
                value={filterState.from}
                onChange={(e) =>
                    setFilterState({ ...filterState, from: e.target.value })
                }
            />

            <Input
                type="date"
                value={filterState.to}
                onChange={(e) =>
                    setFilterState({ ...filterState, to: e.target.value })
                }
            />

            <Button onClick={onApply}>Apply</Button>
        </div>
    );
}
