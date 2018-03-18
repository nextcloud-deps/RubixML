<?php

use Rubix\Graph\ObjectIndex;
use Rubix\Graph\GraphObject;
use PHPUnit\Framework\TestCase;

class ObjectIndexTest extends TestCase
{
    protected $index;

    public function setUp()
    {
        $objects = [
            1 => new GraphObject(['color' => 'blue', 'luminance' => 10]),
            2 => new GraphObject(['color' => 'green', 'luminance' => 40]),
            3 => new GraphObject(['color' => 'red', 'luminance' => 20]),
            4 => new GraphObject(['color' => 'yellow', 'luminance' => 50]),
            'a' => new GraphObject(['color' => 'purple', 'luminance' => 80]),
            'b' => new GraphObject(['color' => 'blue', 'luminance' => 100]),
        ];

        $this->index = new ObjectIndex($objects);
    }

    public function test_get_first()
    {
        $this->assertEquals('blue', $this->index->first()->color);
    }

    public function test_get_last()
    {
        $this->assertEquals(100, $this->index->last()->luminance);
    }

    public function test_get_all()
    {
        $this->assertEquals(6, count($this->index->all()));
    }

    public function test_get_by_key()
    {
        $this->assertEquals('yellow', $this->index->get(4)->color);
    }

    public function test_get_many_by_key()
    {
        $objects = $this->index->mget([1, 2, 3]);

        $this->assertEquals('blue', $objects[1]->color);
        $this->assertEquals('green', $objects[2]->color);
        $this->assertEquals('red', $objects[3]->color);
    }

    public function test_put_object_in_index()
    {
        $this->index->put(1234, new GraphObject(['color' => 'brown']));

        $this->assertEquals('brown', $this->index->get(1234)->color);
    }

    public function test_remove_object_from_index()
    {
        $this->assertEquals('yellow', $this->index->get(4)->color);

        $this->index->remove(4);

        $this->assertNull($this->index->get(4));
    }

    public function test_index_has_object()
    {
        $this->assertTrue($this->index->has(2));
        $this->assertFalse($this->index->has(99));
    }

    public function test_merge_objects_into_index()
    {
        $this->index->merge([
            10 => new GraphObject(['color' => 'white']),
            11 => new GraphObject(['color' => 'black']),
        ]);

        $this->assertEquals('white', $this->index->get(10)->color);
        $this->assertEquals('black', $this->index->get(11)->color);
    }

    public function test_where_clause()
    {
        $this->assertEquals(2, $this->index->where('color', '=', 'blue')->count());
        $this->assertEquals(4, $this->index->where('color', '!=', 'blue')->count());
        $this->assertEquals(4, $this->index->where('color', '<>', 'blue')->count());
        $this->assertEquals(2, $this->index->where('color', 'like', 'blu')->count());
        $this->assertEquals(2, $this->index->where('color', 'like', 'lue')->count());
        $this->assertEquals(2, $this->index->where('luminance', '>', '50')->count());
        $this->assertEquals(3, $this->index->where('luminance', '<', '50')->count());
        $this->assertEquals(4, $this->index->where('luminance', '<=', '50')->count());
        $this->assertEquals(3, $this->index->where('luminance', '>=', '50')->count());
    }

    public function test_where_in_clause()
    {
        $this->assertEquals(3, $this->index->whereIn('color', ['blue', 'red'])->count());
        $this->assertEquals(2, $this->index->whereIn('color', ['red', 'green'])->count());
        $this->assertEquals(1, $this->index->whereIn('color', ['green', 'black'])->count());
    }

    public function test_order_index_by_property()
    {
        $this->assertEquals(['blue', 'green', 'red', 'yellow', 'purple', 'blue'], $this->index->pluck('color'));

        $this->index->orderBy('luminance');

        $this->assertEquals(['blue', 'red', 'green', 'yellow', 'purple', 'blue'], $this->index->pluck('color'));
    }

    public function test_select_property_columns()
    {
        $table = [
            ['blue', 'green', 'red', 'yellow', 'purple', 'blue'],
            [10, 40, 20, 50, 80, 100],
        ];

        $this->assertEquals($table, $this->index->select(['color', 'luminance']));
    }

    public function test_pluck_property()
    {
        $this->assertEquals([10, 40, 20, 50, 80, 100], $this->index->pluck('luminance'));
    }

    public function test_limit()
    {
        $this->assertEquals(3, $this->index->limit(3)->count());
    }

    public function test_skip()
    {
        $this->assertEquals(4, $this->index->skip(2)->count());
    }

    public function test_get_object_keys()
    {
        $this->assertEquals([1, 2, 3, 4, 'a', 'b'], $this->index->keys());
    }
}
