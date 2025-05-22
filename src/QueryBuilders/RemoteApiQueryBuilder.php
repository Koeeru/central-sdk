<?php

namespace Koeeru\Central\QueryBuilders;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use BadMethodCallException;

class RemoteApiQueryBuilder
{
    protected array $wheres = [];
    protected array $orWheres = [];
    protected array $orders = [];
    protected array $selects = [];
    protected int|null $limit = null;
    protected int|null $offset = null;
    protected int $perPage = 15;
    protected int|null $page = null;
    protected $service;
    public function __construct(string $serviceClass)
    {
        if (!class_exists($serviceClass)) {
            throw new BadMethodCallException("Service class {$serviceClass} does not exist.");
        }

        $this->service = app($serviceClass);

        if (!method_exists($this->service, 'all')) {
            throw new BadMethodCallException("Service class {$serviceClass} does not have a all method.");
        }
    }

    // Add where condition
    public function where($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = compact('column', 'operator', 'value');
        return $this;
    }

    // Add OR where condition
    public function orWhere($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->orWheres[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function whereIn(string $column, array $values)
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'in', 'value' => $values];
        return $this;
    }

    public function whereNotIn(string $column, array $values)
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'not_in', 'value' => $values];
        return $this;
    }

    public function whereNull(string $column)
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'null', 'value' => null];
        return $this;
    }

    public function whereNotNull(string $column)
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'not_null', 'value' => null];
        return $this;
    }

    public function select(array $columns)
    {
        $this->selects = $columns;
        return $this;
    }

    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc')
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function orderByDesc(string $column)
    {
        return $this->orderBy($column, 'desc');
    }

    public function count()
    {
        return $this->get()->count();
    }

    public function exists()
    {
        return $this->get()->isNotEmpty();
    }

    public function pluck(string $column, string $key = null)
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

    public function paginate(int $perPage = 15, ?int $page = null)
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

        $collection = collect($rawData);

        // Apply wheres (AND)
        $filtered = $collection->filter(function ($item) {
            foreach ($this->wheres as $where) {
                if (!$this->applyWhere($item, $where)) {
                    return false;
                }
            }

            // Apply OR wheres: nếu có ít nhất 1 điều kiện orWhere đúng thì pass
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
        });

        // Apply select columns if set
        if (!empty($this->selects)) {
            $filtered = $filtered->map(function ($item) {
                return collect($item)->only($this->selects)->toArray();
            });
        }

        // Apply orders
        if (!empty($this->orders)) {
            foreach ($this->orders as $order) {
                $filtered = $filtered->sortBy($order['column'], SORT_REGULAR, $order['direction'] === 'desc');
            }
        }

        // Apply offset and limit
        if ($this->offset !== null) {
            $filtered = $filtered->slice($this->offset);
        }
        if ($this->limit !== null) {
            $filtered = $filtered->take($this->limit);
        }

        return $filtered->values();
    }

    protected function applyWhere($item, array $where): bool
    {
        $val = data_get($item, $where['column']);
        $op = $where['operator'];
        $cmp = $where['value'];

        switch ($op) {
            case '=': return $val == $cmp;
            case '!=': return $val != $cmp;
            case '>': return $val > $cmp;
            case '<': return $val < $cmp;
            case '>=': return $val >= $cmp;
            case '<=': return $val <= $cmp;
            case 'like':
                $needle = strtolower(str_replace('%', '', $cmp));
                return str_contains(strtolower($val), $needle);
            case 'in':
                return in_array($val, $cmp);
            case 'not_in':
                return !in_array($val, $cmp);
            case 'null':
                return is_null($val);
            case 'not_null':
                return !is_null($val);
            default:
                throw new BadMethodCallException("Operator {$op} is not supported.");
        }
    }
}

