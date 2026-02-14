<?php

namespace App\Services\Reports\Base;

use App\DTO\Base\BaseReportFilterDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class BaseReportService
{
    /**
     * Get the model class
     */
    abstract protected function getModel(): string;

    /**
     * Get date column name for filtering
     */
    abstract protected function getDateColumn(): string;

    /**
     * Apply custom filters to query
     */
    abstract protected function applyCustomFilters(Builder $query, BaseReportFilterDTO $dto): void;

    /**
     * Transform data for detail sheet
     */
    abstract protected function transformDetailData($item): array;

    /**
     * Get detail data with all filters applied
     */
    public function getDetailData(BaseReportFilterDTO $dto): Collection
    {
        $query = $this->buildBaseQuery($dto);

        return $query->get()->map(fn($item) => $this->transformDetailData($item));
    }

    /**
     * Get summary data grouped by specified column
     */
    public function getSummaryData(BaseReportFilterDTO $dto, string $groupByColumn): Collection
    {
        $query = $this->buildBaseQuery($dto);

        return $query
            ->select($groupByColumn, DB::raw('COUNT(*) as total'))
            ->groupBy($groupByColumn)
            ->get();
    }

    /**
     * Build base query with common filters
     */
    protected function buildBaseQuery(BaseReportFilterDTO $dto): Builder
    {
        $model = $this->getModel();
        $query = $model::query();

        // Apply date range filter
        if ($dto->hasDateRange()) {
            $query->whereBetween($this->getDateColumn(), [
                $dto->startDate,
                $dto->endDate
            ]);
        }

        // Apply custom filters
        $this->applyCustomFilters($query, $dto);

        // Apply sorting
        $query->orderBy($dto->sortBy, $dto->sortDir);

        return $query;
    }

    /**
     * Get total count
     */
    public function getTotalCount(BaseReportFilterDTO $dto): int
    {
        return $this->buildBaseQuery($dto)->count();
    }

    /**
     * Get aggregate data
     */
    public function getAggregates(BaseReportFilterDTO $dto, array $columns): array
    {
        $query = $this->buildBaseQuery($dto);
        $aggregates = [];

        foreach ($columns as $column => $function) {
            $aggregates[$column] = $query->{$function}($column);
        }

        return $aggregates;
    }
}
