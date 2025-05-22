<?php

namespace Koeeru\Central\QueryBuilders;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use BadMethodCallException;
use Illuminate\Support\LazyCollection;

class RemoteApiQueryBuilder
{
    protected array $wheres = [];
    protected array $orWheres = [];
    protected array $orders = [];
    protected array $selects = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected int $perPage = 15;
    protected ?int $page = null;
    protected $service;
    protected string $eloquentModelClass;


    public function __construct(string $serviceClass, string $eloquentModelClass)
    {
        if (!class_exists($serviceClass)) {
            throw new BadMethodCallException("Service class {$serviceClass} does not exist.");
        }

        if (!class_exists($eloquentModelClass)) {
            throw new BadMethodCallException("Eloquent model class {$eloquentModelClass} does not exist.");
        }

        $this->service = app($serviceClass);
        $this->eloquentModelClass = $eloquentModelClass;

        if (!method_exists($this->service, 'all')) {
            throw new BadMethodCallException("Service class {$serviceClass} does not have an all method.");
        }
    }

    public function where($column, $operator = null, $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->orWheres[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'in', 'value' => $values];
        return $this;
    }

    public function whereNotIn(string $column, array $values): static
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'not_in', 'value' => $values];
        return $this;
    }

    public function whereNull(string $column): static
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'null', 'value' => null];
        return $this;
    }

    public function whereNotNull(string $column): static
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'not_null', 'value' => null];
        return $this;
    }

    public function select(array $columns): static
    {
        $this->selects = $columns;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function orderByDesc(string $column): static
    {
        return $this->orderBy($column, 'desc');
    }

    public function count(): int
    {
        return $this->get()->count();
    }

    public function exists(): bool
    {
        return $this->get()->isNotEmpty();
    }

    public function pluck(string $column, string $key = null): Collection
    {
        return $this->get()->pluck($column, $key);
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function find($id)
    {
        return $this->where('id', $id)->first();
    }

    public function paginate(int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        $this->perPage = $perPage;
        $this->page = $page ?? LengthAwarePaginator::resolveCurrentPage();

        $all = $this->get();

        $items = $all->slice(($this->page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $all->count(),
            $perPage,
            $this->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function get(): Collection
    {
        $rawData = $this->service->all();
        $collection = collect($rawData)->map(function ($attributes) {
            return (new $this->eloquentModelClass())->newFromBuilder($attributes);
        });


        $filtered = $collection->filter(function ($item) {
            foreach ($this->wheres as $where) {
                if (!$this->applyWhere($item, $where)) {
                    return false;
                }
            }

            if (!empty($this->orWheres)) {
                foreach ($this->orWheres as $orWhere) {
                    if ($this->applyWhere($item, $orWhere)) {
                        return true;
                    }
                }
                return false;
            }

            return true;
        });

        if (!empty($this->selects)) {
            $filtered->each->setVisible($this->selects);
        }

        foreach ($this->orders as $order) {
            $filtered = $filtered->sortBy($order['column'], SORT_REGULAR, $order['direction'] === 'desc');
        }

        if ($this->offset !== null) {
            $filtered = $filtered->slice($this->offset);
        }

        if ($this->limit !== null) {
            $filtered = $filtered->take($this->limit);
        }

        return $filtered->values();
    }

    public function cursor(): \Illuminate\Support\LazyCollection
    {
        $rawData = $this->service->all();

        return LazyCollection::make(function () use ($rawData) {
            foreach ($rawData as $attributes) {
                $model = (new $this->eloquentModelClass())->newFromBuilder($attributes);

                // Apply filters (wheres + orWheres)
                if (!$this->passesWhereConditions($model)) {
                    continue;
                }

                yield $model;
            }
        });
    }

    protected function passesWhereConditions($item): bool
    {
        foreach ($this->wheres as $where) {
            if (!$this->applyWhere($item, $where)) {
                return false;
            }
        }

        if (!empty($this->orWheres)) {
            $orPass = false;
            foreach ($this->orWheres as $orWhere) {
                if ($this->applyWhere($item, $orWhere)) {
                    $orPass = true;
                    break;
                }
            }
            if (!$orPass) {
                return false;
            }
        }

        return true;
    }


    protected function applyWhere($item, array $where): bool
    {
        $val = data_get($item, $where['column']);
        $op = $where['operator'];
        $cmp = $where['value'];

        return match ($op) {
            '=' => $val == $cmp,
            '!=' => $val != $cmp,
            '>' => $val > $cmp,
            '<' => $val < $cmp,
            '>=' => $val >= $cmp,
            '<=' => $val <= $cmp,
            'like' => str_contains(strtolower($val), strtolower(str_replace('%', '', $cmp))),
            'in' => in_array($val, $cmp),
            'not_in' => !in_array($val, $cmp),
            'null' => is_null($val),
            'not_null' => !is_null($val),
            default => throw new BadMethodCallException("Operator {$op} is not supported."),
        };
    }
}
