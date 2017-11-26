<?php

namespace App\Components;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Collection;

class Paginator
{
    /*
     * page index  Identifier
     */
    const INDEX = 'page_index';

    /**
     * page size Identifier.
     */
    const SIZE = 'page_size';

    private $index;

    private $size;

    private $hasMore;

    private $count;

    private $collection;

    /**
     * construct.
     *
     * @param HttpRequest $request
     */
    public function __construct(HttpRequest $request)
    {
        $this->index = $request->get(self::INDEX, 1);
        $this->size  = $request->get(self::SIZE,  20);
        $this->hasMore = 0;
        $this->count = 0;
    }

    /**
     * Query DB Collection.
     *
     * @param Builder $query
     * @return mixed
     */
    public function query(Builder $query)
    {
        $this->count      = $query->count();
        $this->collection = $query->forPage($this->index, $this->size)->get();
        $this->hasMore    = $this->index * $this->size < $this->count ? 1 : 0;

        return $this->collection;
    }

    /**
     * Query Array Collection.
     *
     * @param $items
     * @return Collection
     */
    public function queryArray($items)
    {
        if (! $items instanceof Collection && is_array($items)) {
            $items  = new Collection($items);
        }
        $collection         = $items;
        $offset             = ($this->index - 1) * $this->size;
        $this->count        = $collection->count();
        $this->collection   = $collection->slice($offset, $this->size);
        $this->hasMore      = $this->index * $this->size < $this->count ? 1 : 0;

        return $this->collection;
    }

    /**
     * export page info.
     *
     * @return array
     */
    public function export()
    {
        return [
            'index'     => $this->index,
            'size'      => $this->size,
            'count'     => $this->count,
            'has_more'  => $this->hasMore,
        ];
    }
}
