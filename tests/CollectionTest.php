<?php
/**
 * Collection
 * Copyright 2016-2018 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Collection/blob/master/LICENSE
**/

namespace CharlotteDunois\Collect\Tests;

use BadMethodCallException;
use CharlotteDunois\Collect\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest
 *
 * @package CharlotteDunois\Collect\Tests
 */
class CollectionTest extends TestCase
{
    public function testCreateWithDataAndGetAll(): void
    {
        $array = [5, 5, 20];
        $collection = new Collection($array);

        $this->assertSame($array, $collection->all());
    }

    public function testCountable(): void
    {
        $collection = new Collection([5, 5, 20]);
        $this->assertCount(3, $collection);
    }

    public function testIterator(): void
    {
        $array = [5, 5, 20];
        $collection = new Collection($array);
        $index = 0;

        foreach ($collection as $key => $val) {
            $this->assertSame($index, $key);
            $this->assertSame($array[$index], $val);
            $index++;
        }
    }

    public function testHas(): void
    {
        $collection = new Collection();

        $this->assertFalse($collection->has(0));
        $this->assertSame($collection, $collection->set(0, 500));
        $this->assertTrue($collection->has(0));
    }

    public function testGetSet(): void
    {
        $collection = new Collection();

        $this->assertSame($collection, $collection->set(0, 500));
        $this->assertSame(500, $collection->get(0));
        $this->assertNull($collection->get(1));
    }

    public function testDelete(): void
    {
        $collection = new Collection();

        $this->assertSame($collection, $collection->set(0, 500));
        $this->assertTrue($collection->has(0));

        $this->assertSame($collection, $collection->delete(0));
        $this->assertFalse($collection->has(0));
    }

    public function testClear(): void
    {
        $collection = new Collection();

        $collection->set(0, 500);
        $this->assertSame([500], $collection->all());

        $collection->clear();
        $this->assertSame([], $collection->all());
    }

    public function testChunk(): void
    {
        $array = [5, 5, 20];
        $collection = new Collection($array);
        $collection2 = $collection->chunk(2);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);

        $this->assertSame([[5, 5], [20]], $collection2->all());
    }

    public function testCount(): void
    {
        $collection = new Collection([15, 42]);
        $this->assertSame(2, $collection->count());
    }

    public function testCopy(): void
    {
        $collection = new Collection([15, 42]);

        $collection2 = $collection->copy();
        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);

        $this->assertSame($collection->all(), $collection2->all());
    }

    public function testDiff(): void
    {
        $collection = new Collection([15, 42]);
        $diff = $collection->diff(array(15));

        $this->assertInstanceOf(Collection::class, $diff);
        $this->assertNotEquals($collection, $diff);
        $this->assertSame([1 => 42], $diff->all());
    }

    public function testDiffCollection(): void
    {
        $collection = new Collection([15, 42]);
        $diff = $collection->diff((new Collection([15])));

        $this->assertInstanceOf(Collection::class, $diff);
        $this->assertNotEquals($collection, $diff);
        $this->assertSame([1 => 42], $diff->all());
    }

    public function testDiffKeys(): void
    {
        $collection = new Collection([15, 42]);
        $diff = $collection->diffKeys((new Collection([0 => 42])));

        $this->assertInstanceOf(Collection::class, $diff);
        $this->assertNotEquals($collection, $diff);
        $this->assertSame([1 => 42], $diff->all());
    }

    public function testDiffKeysCollection(): void
    {
        $collection = new Collection([15, 42]);
        $diff = $collection->diffKeys((new Collection([1 => 42])));

        $this->assertInstanceOf(Collection::class, $diff);
        $this->assertNotEquals($collection, $diff);
        $this->assertSame([0 => 15], $diff->all());
    }

    public function testEach(): void
    {
        $collection = new Collection(array(15, 42));
        $index = 0;

        $collection->each(static function ($value, $key) use (&$index): void {
            if ($index === 0) {
                $this->assertSame(15, $value);
                $this->assertSame(0, $key);
            } elseif($index === 1) {
                $this->assertSame(42, $value);
                $this->assertSame(1, $key);
            } else {
                throw new \RuntimeException('Unexpected invocation');
            }

            $index++;
        });

        $this->assertSame(2, $index);
    }

    public function testEvery(): void
    {
        $collection = new Collection([15, 42]);

        $this->assertTrue($collection->every(static function ($value, $key): bool {
            return ($value > 10 && $key < 2);
        }));
    }

    public function testEveryFailure(): void
    {
        $collection = new Collection([15, 42]);

        $this->assertFalse($collection->every(static function ($value, $key): bool {
            return ($value < 40 && $key < 2);
        }));
    }

    public function testExcept(): void
    {
        $collection = new Collection([15, 42, 30, 25, 50]);
        $collection2 = $collection->except(array(0, 3));

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([1 => 42, 2 => 30, 4 => 50], $collection2->all());
    }

    public function testFilter(): void
    {
        $collection = new Collection(array(15, 42, 30, 25, 50));
        $collection2 = $collection->filter(static function ($value, $key): bool {
            return ($value < 40 || $key < 2);
        });

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([0 => 15, 1 => 42, 2 => 30, 3 => 25], $collection2->all());
    }

    public function testFirst(): void
    {
        $collection = new Collection([15, 42, 30, 25, 50]);
        $this->assertSame(15, $collection->first());

        $collection2 = new Collection();
        $this->assertNull($collection2->first());
    }

    public function testFirstCallback(): void
    {
        $collection = new Collection([15, 42, 30, 25, 50]);
        $this->assertSame(50, $collection->first(static function ($value, $key): bool {
            return ($value > 45 && $key < 5);
        }));

        $collection2 = new Collection();
        $this->assertNull($collection2->first(static function ($value, $key): bool {
            return ($value > 60 || $key > 5);
        }));
    }

    public function testFlatten(): void
    {
        $collection = new Collection([15, [42, [30, 52, [50]]], (new Collection([25, 50]))]);
        $collection2 = $collection->flatten();

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([15, 42, 30, 52, 50, 25, 50], $collection2->all());
    }

    public function testFlattenDepth(): void
    {
        $collection = new Collection([15, [42, [30, 52, [50]]], (new Collection([25, 50]))]);
        $collection2 = $collection->flatten(1);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([15, 42, [30, 52, [50]], 25, 50], $collection2->all());
    }

    public function testGroupByEmptyColumn(): void
    {
        $collection = new Collection([15, 42, 30, 25, 50]);

        $this->assertSame($collection, $collection->groupBy(null));
        $this->assertSame($collection, $collection->groupBy(''));
    }

    public function testGroupByCallback(): void
    {
        $collection = new Collection([15, 42, 30, 25, 50]);
        $collection2 = $collection->groupBy(static function ($value, $key): bool {
            return ((int) ($value > 27 || $key > 5));
        });

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([[15, 25], [42, 30, 50]], $collection2->all());
    }

    public function testGroupByArray(): void
    {
        $value = [
            ['key2' => 0, 'val' => 15],
            ['key2' => 1, 'val' => 42],
            ['key2' => 1, 'val' => 30],
            ['key2' => 0, 'val' => 25],
            ['key2' => 1, 'val' => 50]
        ];

        $collection = new Collection($value);
        $collection2 = $collection->groupBy('key2');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);

        $target = [
            [['key2' => 0, 'val' => 15], ['key2' => 0, 'val' => 25]],
            [['key2' => 1, 'val' => 42], ['key2' => 1, 'val' => 30], ['key2' => 1, 'val' => 50]]
        ];

        $this->assertSame($target, $collection2->all());
    }

    public function testGroupByObject(): void
    {
        $value = [
            ((object) ['key2' => 0, 'val' => 15]),
            ((object) ['key2' => 1, 'val' => 42]),
            ((object) ['key2' => 1, 'val' => 30]),
            ((object) ['key2' => 0, 'val' => 25]),
            ((object) ['key2' => 1, 'val' => 50])
        ];

        $collection = new Collection($value);
        $collection2 = $collection->groupBy('key2');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);

        $target = [
            [((object) ['key2' => 0, 'val' => 15]), ((object) ['key2' => 0, 'val' => 25])],
            [((object) ['key2' => 1, 'val' => 42]), ((object) ['key2' => 1, 'val' => 30]), ((object) ['key2' => 1, 'val' => 50])]
        ];

        $this->assertEquals($target, $collection2->all());
    }

    public function testImplode(): void
    {
        $collection = new Collection([15, 42, 30]);
        $this->assertSame('15, 42, 30', $collection->implode(null, ', '));
    }

    public function testImplodeArray(): void
    {
        $value = [['k' => 15], ['k' => 42], ['k' => 30]];
        $collection = new Collection($value);

        $this->assertSame('15, 42, 30', $collection->implode('k', ', '));
    }

    public function testImplodeArrayFailure(): void
    {
        $value = [['k' => 15], ['k' => 42], ['k' => 30]];
        $collection = new Collection($value);

        $this->expectException(BadMethodCallException::class);
        $collection->implode('v', ', ');
    }

    public function testImplodeObject(): void
    {
        $value = [
            ((object) ['k' => 15]),
            ((object) ['k' => 42]),
            ((object) ['k' => 30])
        ];

        $collection = new Collection($value);

        $this->assertSame('15, 42, 30', $collection->implode('k', ', '));
    }

    public function testImplodeObjectFailure(): void
    {
        $value = [
            ((object) ['k' => 15]),
            ((object) ['k' => 42]),
            ((object) ['k' => 30])
        ];

        $collection = new Collection($value);

        $this->expectException(BadMethodCallException::class);
        $collection->implode('v', ', ');
    }

    public function testIndexOf(): void
    {
        $collection = new Collection([15, 42, 30]);
        $this->assertSame(1, $collection->indexOf(42));
    }

    public function testIndexOfNull(): void
    {
        $collection = new Collection([15, 42, 30]);
        $this->assertNull($collection->indexOf(50));
    }

    public function testIntersect(): void
    {
        $collection = new Collection([15, 42, 30]);
        $collection2 = $collection->intersect([15, 30]);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([15, 2 => 30], $collection2->all());
    }

    public function testIntersectCollection(): void
    {
        $collection = new Collection([15, 42, 30]);
        $collection2 = $collection->intersect((new Collection([15, 42])));

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([15, 42], $collection2->all());
    }

    public function testKeys(): void
    {
        $collection = new Collection([15, 42, 30]);
        $collection2 = $collection->keys();

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([0, 1, 2], $collection2->all());
    }

    public function testLast(): void
    {
        $collection = new Collection([15, 42, 30, 25, 50]);
        $this->assertSame(50, $collection->last());

        $collection2 = new Collection();
        $this->assertNull($collection2->last());
    }

    public function testLastCallback(): void
    {
        $collection = new Collection([15, 42, 30, 25, 50]);
        $this->assertSame(25, $collection->last(static function ($value, $key): bool {
            return ($value < 45 && $key < 5);
        }));

        $collection2 = new Collection();
        $this->assertNull($collection2->last(static function ($value, $key): bool {
            return ($value > 60 || $key > 5);
        }));
    }

    public function testMap(): void
    {
        $collection = new Collection([15, 42, 30]);
        $collection2 = $collection->map(static function ($value, $key): bool {
            return (($value * 2) + $key);
        });

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([30, 85, 62], $collection2->all());
    }

    public function testMax(): void
    {
        $collection = new Collection([15, 42, 30]);
        $this->assertSame(42, $collection->max());
    }

    public function testMaxKey(): void
    {
        $value = [['k' => 15], ['k' => 42], ['k' => 30]];
        $collection = new Collection($value);

        $this->assertSame(42, $collection->max('k'));
    }

    public function testMin(): void
    {
        $collection = new Collection([15, 42, 30]);
        $this->assertSame(15, $collection->min());
    }

    public function testMinKey(): void
    {
        $value = [['k' => 15], ['k' => 42], ['k' => 30]];
        $collection = new Collection($value);

        $this->assertSame(15, $collection->min('k'));
    }

    public function testMerge(): void
    {
        $collection = new Collection([15, 42, 30]);
        $collection2 = $collection->merge((new Collection([40, 0])));

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([15, 42, 30, 40, 0], $collection2->all());
    }

    public function testNth(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->nth(2);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([15, 30, 0], $collection2->all());
    }

    public function testNthOffset(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->nth(2, 1);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([42, 40], $collection2->all());
    }

    public function testNth0nth(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);

        $this->expectException(InvalidArgumentException::class);
        $collection->nth(0);
    }

    public function testNthMinusnth(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);

        $this->expectException(InvalidArgumentException::class);
        $collection->nth(-1);
    }

    public function testOnly(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->only([0, 4]);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([0 => 15, 4 => 0], $collection2->all());
    }

    public function testPartition(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collectionArray = $collection->partition(static function ($value, $key): bool {
            return ($value > 30 || $key === -1);
        });

        $this->assertIsArray($collectionArray);
        $this->assertCount(2, $collectionArray);
        $this->assertInstanceOf(Collection::class, $collectionArray[0]);
        $this->assertInstanceOf(Collection::class, $collectionArray[1]);
        $this->assertSame([1 => 42, 3 => 40], $collectionArray[0]->all());
        $this->assertSame([0 => 15, 2 => 30, 4 => 0], $collectionArray[1]->all());
    }

    public function testPluck(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->pluck('k');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([], $collection2->all());
    }

    public function testPluckArray(): void
    {
        $value = [
            ['key2' => 5, 'val' => 15],
            ['key2' => 2, 'val' => 42],
            ['key2' => 10, 'val' => 30],
            ['key2' => 12, 'val' => 25],
            ['key2' => 42, 'val' => 50]
        ];

        $collection = new Collection($value);
        $collection2 = $collection->pluck('val', 'key2');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);

        $target = [5 => 15, 2 => 42, 10 => 30, 12 => 25, 42 => 50];

        $this->assertSame($target, $collection2->all());
    }

    public function testPluckObject(): void
    {
        $value = [
            ((object) ['key2' => 5, 'val' => 15]),
            ((object) ['key2' => 2, 'val' => 42]),
            ((object) ['key2' => 10, 'val' => 30]),
            ((object) ['key2' => 12, 'val' => 25]),
            ((object) ['key2' => 42, 'val' => 50])
        ];

        $collection = new Collection($value);
        $collection2 = $collection->pluck('val', 'key2');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);

        $target = [5 => 15, 2 => 42, 10 => 30, 12 => 25, 42 => 50];
        $this->assertSame($target, $collection2->all());
    }

    public function testRandom(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->random(1);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame(1, $collection2->count());
    }

    public function testRandomMultiple(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->random(3);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame(3, $collection2->count());
    }

    public function testReduce(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);

        $carry = $collection->reduce(static function ($carry, $value): int {
            return ($carry + $value);
        }, 5);

        $this->assertSame(132, $carry);
    }

    public function testReverse(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->reverse();

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([0, 40, 30, 42, 15], $collection2->all());
    }

    public function testReversePreserve(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->reverse(true);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);
        $this->assertSame([4 => 0, 3 => 40, 2 => 30, 1 => 42, 0 => 15], $collection2->all());
    }

    public function testSearch(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $this->assertSame(2, $collection->search(30, false));
    }

    public function testSearchStrict(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $this->assertFalse($collection->search('15', true));
    }

    public function testShuffle(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->shuffle();

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
    }

    public function testSlice(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->slice(1, 2);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([42, 30], $collection2->all());
    }

    public function testSlicePreserve(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->slice(1, 2, true);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame([1 => 42, 2 => 30], $collection2->all());
    }

    public function testSome(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);

        $this->assertTrue($collection->some(static function ($value, $key): bool {
            return ($value > 20 && $key > 2);
        }));
    }

    public function testSomeFailure(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);

        $this->assertFalse($collection->some(static function ($value, $key): bool {
            return ($value > 50 && $key === 0);
        }));
    }

    public function testSort(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->sort();

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);
        $this->assertSame([4 => 0, 0 => 15, 2 => 30, 3 => 40, 1 => 42], $collection2->all());
    }

    public function testSortDescending(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->sort(true);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);
        $this->assertSame([1 => 42, 3 => 40, 2 => 30, 0 => 15, 4 => 0], $collection2->all());
    }

    public function testSortKey(): void
    {
        $collection = new Collection([4 => 0, 0 => 15, 2 => 30, 3 => 40, 1 => 42]);
        $collection2 = $collection->sortKey();

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);
        $this->assertSame([15, 42, 30, 40, 0], $collection2->all());
    }

    public function testSortKeyDescending(): void
    {
        $collection = new Collection([4 => 0, 0 => 15, 2 => 30, 3 => 40, 1 => 42]);
        $collection2 = $collection->sortKey(true);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);
        $this->assertSame([4 => 0, 3 => 40, 2 => 30, 1 => 42, 0 => 15], $collection2->all());
    }

    public function testSortCustom(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->sortCustom(static function ($a, $b): bool {
            return ($b <=> $a);
        });

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);
        $this->assertSame([1 => 42, 3 => 40, 2 => 30, 0 => 15, 4 => 0], $collection2->all());
    }

    public function testSortCustomKey(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0]);
        $collection2 = $collection->sortCustomKey(static function ($a, $b): bool {
            return ($b <=> $a);
        });

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotSame($collection, $collection2);
        $this->assertSame([4 => 0, 3 => 40, 2 => 30, 1 => 42,  0 => 15], $collection2->all());
    }

    public function testUnique(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0, 40, 42, 30, 0, 1]);
        $collection2 = $collection->unique(null);

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
    }

    public function testUniqueWithKeyOnScalar(): void
    {
        $collection = new Collection([15, 42, 30, 40, 0, 40, 42, 30, 0, 1]);
        $collection2 = $collection->unique('k');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
    }

    public function testUniqueKeyArray(): void
    {
        $values = [['k' => 15], ['k' => 42], ['k' => 30], ['k' => 30], ['k' => 15]];

        $collection = new Collection($values);
        $collection2 = $collection->unique('k');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);

        $target = [['k' => 15], ['k' => 42], ['k' => 30]];
        $this->assertSame($target, $collection2->all());
    }

    public function testUniqueKeyArrayFailure(): void
    {
        $values = [[15], [42]];
        $collection = new Collection($values);

        $this->expectException(BadMethodCallException::class);
        $collection->unique('val');
    }

    public function testUniqueKeyObject(): void
    {
        $values = [
            ((object) ['k' => 15]),
            ((object) ['k' => 42]),
            ((object) ['k' => 30]),
            ((object) ['k' => 30]),
            ((object) ['k' => 15])
        ];

        $collection = new Collection($values);
        $collection2 = $collection->unique('k');

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);

        $target = [
            ((object) ['k' => 15]),
            ((object) ['k' => 42]),
            ((object) ['k' => 30])
        ];

        $this->assertEquals($target, $collection2->all());
    }

    public function testUniqueKeyObjectFailure(): void
    {
        $value = [((object) [15]), ((object) [42])];
        $collection = new Collection($value);

        $this->expectException(BadMethodCallException::class);
        $collection->unique('val');
    }

    public function testValues(): void
    {
        $array = [5 => 15, 4 => 42, 30, 0 => 40, 'a' => 0, 40, 42, 30, 0, 1];
        $collection = new Collection($array);
        $collection2 = $collection->values();

        $this->assertInstanceOf(Collection::class, $collection2);
        $this->assertNotEquals($collection, $collection2);
        $this->assertSame(\array_values($array), $collection2->all());
    }
}
