<?php

namespace Stackstra\Tests\Stream;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Stream\Stream;
use Stackstra\Tests\TestCase;

#[CoversClass(Stream::class)]
class StreamTest extends TestCase
{
    public function testConstruct(): void
    {
        // no arguments: empty state
        $stream = new Stream();
        $this->assertNull($stream->get());
        $this->assertSame(0, $stream->length());
        $this->assertSame(0, $stream->offset());

        // data only: size is computed automatically via Strings::size()
        $stream = new Stream('hello');
        $this->assertSame('hello', $stream->get());
        $this->assertSame(5, $stream->length());

        // explicit data_size overrides the computed size
        $stream = new Stream('hello', 3);
        $this->assertSame(3, $stream->length());
    }

    public function testMake(): void
    {
        $stream = Stream::make('hello', 3);

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('hello', $stream->get());
        $this->assertSame(3, $stream->length());
    }

    public function testInitialize(): void
    {
        // idempotent: calling it repeatedly (as the constructor already does) doesn't error or change behavior
        Stream::initialize();
        Stream::initialize();

        $stream = new Stream();
        $this->assertSame("\x05", $stream->pack(Stream::TYPE_INT_8, 5));
    }

    public function testSet(): void
    {
        $stream = new Stream();

        // data_size omitted: defaults to 0 regardless of the actual data length (unlike the constructor, which
        // passes an explicit null and triggers the auto-compute branch)
        $stream->set('hello');
        $this->assertSame('hello', $stream->get());
        $this->assertSame(0, $stream->length());

        // data_size explicitly null: computed via Strings::size()
        $stream->set('hello', null);
        $this->assertSame(5, $stream->length());

        // explicit data_size
        $stream->set('hello', 2);
        $this->assertSame(2, $stream->length());

        // resets the offset back to 0
        $stream->offset(1);
        $stream->set('world');
        $this->assertSame(0, $stream->offset());

        // no data: data_size defaults to 0
        $stream->set();
        $this->assertNull($stream->get());
        $this->assertSame(0, $stream->length());
    }

    public function testOffset(): void
    {
        $stream = new Stream('hello');

        // no argument: getter, starts at 0
        $this->assertSame(0, $stream->offset());

        // explicit argument: setter, and it returns the new value
        $this->assertSame(3, $stream->offset(3));
        $this->assertSame(3, $stream->offset());
    }

    public function testLength(): void
    {
        $this->assertSame(0, (new Stream())->length());
        $this->assertSame(5, (new Stream('hello'))->length());
        $this->assertSame(2, (new Stream('hello', 2))->length());
    }

    public function testGet(): void
    {
        $this->assertNull((new Stream())->get());
        $this->assertSame('hello', (new Stream('hello'))->get());
    }

    public function testGetChunked(): void
    {
        // default chunk_size=1
        $this->assertSame(['a', 'b', 'c'], (new Stream('abc'))->getChunked());

        // explicit chunk_size
        $this->assertSame(['ab', 'cd', 'ef'], (new Stream('abcdef'))->getChunked(2));

        // no data: empty array
        $this->assertSame([], (new Stream())->getChunked());
    }

    public function testReset(): void
    {
        $stream = new Stream('hello');
        $stream->offset(3);

        // with a new data argument
        $stream->reset('world');
        $this->assertSame('world', $stream->get());
        $this->assertSame(0, $stream->length()); // data_size is always reset to 0, regardless of the new data
        $this->assertSame(0, $stream->offset());

        // no argument: data becomes null
        $stream->reset();
        $this->assertNull($stream->get());
        $this->assertSame(0, $stream->length());
    }

    public function testPack(): void
    {
        $stream = new Stream();

        // array of values
        $this->assertSame("\x01\x02\x03", $stream->pack(Stream::TYPE_INT_8, [1, 2, 3]));

        // single scalar value
        $this->assertSame("\x05", $stream->pack(Stream::TYPE_INT_8, 5));
    }

    public function testUnpack(): void
    {
        $stream = new Stream(pack('c3', 10, -20, 30));

        // units omitted (null): returns the full unpack() array, keyed from 1, and consumes the rest of the data
        $result = $stream->unpack(Stream::TYPE_INT_8, length: 3);
        $this->assertSame([1 => 10, 2 => -20, 3 => 30], $result);
        $this->assertSame(3, $stream->offset());

        // units=1: unwraps to a single scalar via reset(), and length defaults from $units when omitted
        $stream = new Stream(pack('c3', 10, -20, 30));
        $this->assertSame(10, $stream->unpack(Stream::TYPE_INT_8, units: 1));
        $this->assertSame(1, $stream->offset()); // advanced by the resolved length (1 * size)

        // explicit offset: reads from that position instead of the internal offset, and still advances the internal offset
        $stream = new Stream(pack('c3', 10, -20, 30));
        $this->assertSame(-20, $stream->unpack(Stream::TYPE_INT_8, length: 1, offset: 1, units: 1));

        // length omitted and units omitted: reads to the end of the data and sets offset to data_size
        $stream = new Stream(pack('c3', 10, -20, 30));
        $stream->unpack(Stream::TYPE_INT_8);
        $this->assertSame(3, $stream->offset());
    }

    public function testEncode(): void
    {
        // array of values
        $this->assertSame("\x01\x02\x03", Stream::encode(Stream::TYPE_INT_8, [1, 2, 3]));

        // single scalar value
        $this->assertSame("\x05", Stream::encode(Stream::TYPE_INT_8, 5));
    }

    public function testDecode(): void
    {
        $encoded = Stream::encode(Stream::TYPE_INT_8, [10, -20, 30]);

        $this->assertSame([1 => 10, 2 => -20, 3 => 30], Stream::decode(Stream::TYPE_INT_8, $encoded));
    }

    public function testEncodeInt8(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_INT_8, [1, 2]), Stream::encodeInt8([1, 2]));
    }

    public function testEncodeInt16(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_INT_16, [1, 2]), Stream::encodeInt16([1, 2]));
    }

    public function testEncodeInt32(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_INT_32, [1, 2]), Stream::encodeInt32([1, 2]));
    }

    public function testEncodeInt64(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_INT_64, [1, 2]), Stream::encodeInt64([1, 2]));
    }

    public function testEncodeUint8(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_UINT_8, [1, 2]), Stream::encodeUint8([1, 2]));
    }

    public function testEncodeUint16(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_UINT_16, [1, 2]), Stream::encodeUint16([1, 2]));
    }

    public function testEncodeUint32(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_UINT_32, [1, 2]), Stream::encodeUint32([1, 2]));
    }

    public function testEncodeUint64(): void
    {
        $this->assertSame(Stream::encode(Stream::TYPE_UINT_64, [1, 2]), Stream::encodeUint64([1, 2]));
    }

    public function testDecodeInt8(): void
    {
        $this->assertSame(Stream::decode(Stream::TYPE_INT_8, "\x01\x02"), Stream::decodeInt8("\x01\x02"));
    }

    public function testDecodeInt16(): void
    {
        $encoded = Stream::encodeInt16([1, 2]);
        $this->assertSame(Stream::decode(Stream::TYPE_INT_16, $encoded), Stream::decodeInt16($encoded));
    }

    public function testDecodeInt32(): void
    {
        $encoded = Stream::encodeInt32([1, 2]);
        $this->assertSame(Stream::decode(Stream::TYPE_INT_32, $encoded), Stream::decodeInt32($encoded));
    }

    public function testDecodeInt64(): void
    {
        $encoded = Stream::encodeInt64([1, 2]);
        $this->assertSame(Stream::decode(Stream::TYPE_INT_64, $encoded), Stream::decodeInt64($encoded));
    }

    public function testDecodeUint8(): void
    {
        $this->assertSame(Stream::decode(Stream::TYPE_UINT_8, "\x01\x02"), Stream::decodeUint8("\x01\x02"));
    }

    public function testDecodeUint16(): void
    {
        $encoded = Stream::encodeUint16([1, 2]);
        $this->assertSame(Stream::decode(Stream::TYPE_UINT_16, $encoded), Stream::decodeUint16($encoded));
    }

    public function testDecodeUint32(): void
    {
        $encoded = Stream::encodeUint32([1, 2]);
        $this->assertSame(Stream::decode(Stream::TYPE_UINT_32, $encoded), Stream::decodeUint32($encoded));
    }

    public function testDecodeUint64(): void
    {
        $encoded = Stream::encodeUint64([1, 2]);
        $this->assertSame(Stream::decode(Stream::TYPE_UINT_64, $encoded), Stream::decodeUint64($encoded));
    }

    public function testWrite(): void
    {
        $stream = new Stream();

        $result = $stream->write(Stream::TYPE_INT_8, 5);

        $this->assertSame($stream, $result);
        $this->assertSame("\x05", $stream->get());
    }

    public function testWriteInt8(): void
    {
        $stream = (new Stream())->writeInt8(-100);
        $this->assertSame(-100, (new Stream($stream->get()))->readInt8(1));
    }

    public function testWriteInt16(): void
    {
        $stream = (new Stream())->writeInt16(-30000);
        $this->assertSame(-30000, (new Stream($stream->get()))->readInt16(1));
    }

    public function testWriteInt32(): void
    {
        $stream = (new Stream())->writeInt32(-2000000000);
        $this->assertSame(-2000000000, (new Stream($stream->get()))->readInt32(1));
    }

    public function testWriteInt64(): void
    {
        $stream = (new Stream())->writeInt64(-9000000000000000000);
        $this->assertSame(-9000000000000000000, (new Stream($stream->get()))->readInt64(1));
    }

    public function testWriteUint8(): void
    {
        $stream = (new Stream())->writeUint8(250);
        $this->assertSame(250, (new Stream($stream->get()))->readUint8(1));
    }

    public function testWriteUint16(): void
    {
        $stream = (new Stream())->writeUint16(60000);
        $this->assertSame(60000, (new Stream($stream->get()))->readUint16(1));
    }

    public function testWriteUint32(): void
    {
        $stream = (new Stream())->writeUint32(4000000000);
        $this->assertSame(4000000000, (new Stream($stream->get()))->readUint32(1));
    }

    public function testWriteUint64(): void
    {
        $stream = (new Stream())->writeUint64(9000000000000000000);
        $this->assertSame(9000000000000000000, (new Stream($stream->get()))->readUint64(1));
    }

    public function testWriteArrayInt8(): void
    {
        $data = [1 => 10, 2 => -20, 3 => 30];

        $stream = (new Stream())->writeArrayInt8($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt8());
    }

    public function testReadArrayInt8(): void
    {
        $data = [1 => 10, 2 => -20, 3 => 30];

        // default: both keys and values are saved/read, and combined back into the original mapping
        $stream = (new Stream())->writeArrayInt8($data);
        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt8());

        // values only: returned as a plain list, keys are not persisted
        $stream = (new Stream())->writeArrayInt8($data, save_keys: false, save_values: true);
        $this->assertSame(array_values($data), array_values((new Stream($stream->get()))->readArrayInt8(read_keys: false, read_values: true)));

        // keys only: the original keys are restored, each mapped to null (the values were never saved)
        $stream = (new Stream())->writeArrayInt8($data, save_keys: true, save_values: false);
        $this->assertSame(array_fill_keys(array_keys($data), null), (new Stream($stream->get()))->readArrayInt8(read_keys: true, read_values: false));

        // neither keys nor values requested: empty array, nothing consumed
        $stream = (new Stream())->writeArrayInt8($data);
        $this->assertSame([], (new Stream($stream->get()))->readArrayInt8(read_keys: false, read_values: false));

        // empty source array
        $stream = (new Stream())->writeArrayInt8([]);
        $this->assertSame([], (new Stream($stream->get()))->readArrayInt8());
    }

    public function testWriteArrayInt16(): void
    {
        $data = [1 => 300, 2 => -300];

        $stream = (new Stream())->writeArrayInt16($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt16());
    }

    public function testReadArrayInt16(): void
    {
        $data = [1 => 300, 2 => -300];

        $stream = (new Stream())->writeArrayInt16($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt16());
    }

    public function testWriteArrayInt32(): void
    {
        $data = [1 => 100000, 2 => -100000];

        $stream = (new Stream())->writeArrayInt32($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt32());
    }

    public function testReadArrayInt32(): void
    {
        $data = [1 => 100000, 2 => -100000];

        $stream = (new Stream())->writeArrayInt32($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt32());
    }

    public function testWriteArrayInt64(): void
    {
        $data = [1 => 5000000000, 2 => -5000000000];

        $stream = (new Stream())->writeArrayInt64($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt64());
    }

    public function testReadArrayInt64(): void
    {
        $data = [1 => 5000000000, 2 => -5000000000];

        $stream = (new Stream())->writeArrayInt64($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayInt64());
    }

    public function testWriteArrayUint8(): void
    {
        $data = [1 => 200, 2 => 250];

        $stream = (new Stream())->writeArrayUint8($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint8());
    }

    public function testReadArrayUint8(): void
    {
        $data = [1 => 200, 2 => 250];

        $stream = (new Stream())->writeArrayUint8($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint8());
    }

    public function testWriteArrayUint16(): void
    {
        $data = [1 => 50000, 2 => 60000];

        $stream = (new Stream())->writeArrayUint16($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint16());
    }

    public function testReadArrayUint16(): void
    {
        $data = [1 => 50000, 2 => 60000];

        $stream = (new Stream())->writeArrayUint16($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint16());
    }

    public function testWriteArrayUint32(): void
    {
        $data = [1 => 3000000000, 2 => 4000000000];

        $stream = (new Stream())->writeArrayUint32($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint32());
    }

    public function testReadArrayUint32(): void
    {
        $data = [1 => 3000000000, 2 => 4000000000];

        $stream = (new Stream())->writeArrayUint32($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint32());
    }

    public function testWriteArrayUint64(): void
    {
        $data = [1 => 9000000000000000000, 2 => 5000000000000000000];

        $stream = (new Stream())->writeArrayUint64($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint64());
    }

    public function testReadArrayUint64(): void
    {
        $data = [1 => 9000000000000000000, 2 => 5000000000000000000];

        $stream = (new Stream())->writeArrayUint64($data);

        $this->assertSame($data, (new Stream($stream->get()))->readArrayUint64());
    }

    public function testWriteNull(): void
    {
        $stream = new Stream();

        $stream->writeNull();

        $this->assertSame("\0", $stream->get());
    }

    public function testWriteString(): void
    {
        // length omitted: computed via Strings::size()
        $stream = new Stream();
        $stream->writeString('hi');
        $this->assertSame(pack('V', 2) . 'hi', $stream->get());

        // explicit length overrides the computed size
        $stream = new Stream();
        $stream->writeString('hi', 5);
        $this->assertSame(pack('V', 5) . 'hi', $stream->get());
    }

    public function testRead(): void
    {
        $stream = new Stream(pack('c', 5));

        $this->assertSame(5, $stream->read(Stream::TYPE_INT_8, units: 1));
    }

    public function testReadInt8(): void
    {
        $data = (new Stream())->writeInt8(-100)->get();
        $this->assertSame(-100, (new Stream($data))->readInt8(1));
    }

    public function testReadInt16(): void
    {
        $data = (new Stream())->writeInt16(-30000)->get();
        $this->assertSame(-30000, (new Stream($data))->readInt16(1));
    }

    public function testReadInt32(): void
    {
        $data = (new Stream())->writeInt32(-2000000000)->get();
        $this->assertSame(-2000000000, (new Stream($data))->readInt32(1));
    }

    public function testReadInt64(): void
    {
        $data = (new Stream())->writeInt64(-9000000000000000000)->get();
        $this->assertSame(-9000000000000000000, (new Stream($data))->readInt64(1));
    }

    public function testReadUint8(): void
    {
        $data = (new Stream())->writeUint8(250)->get();
        $this->assertSame(250, (new Stream($data))->readUint8(1));
    }

    public function testReadUint16(): void
    {
        $data = (new Stream())->writeUint16(60000)->get();
        $this->assertSame(60000, (new Stream($data))->readUint16(1));
    }

    public function testReadUint32(): void
    {
        $data = (new Stream())->writeUint32(4000000000)->get();
        $this->assertSame(4000000000, (new Stream($data))->readUint32(1));
    }

    public function testReadUint64(): void
    {
        $data = (new Stream())->writeUint64(9000000000000000000)->get();
        $this->assertSame(9000000000000000000, (new Stream($data))->readUint64(1));
    }

    public function testReadString(): void
    {
        // default offset: the length prefix and the string content are both read from the internal offset
        $stream = new Stream();
        $stream->writeString('hello');
        $stream = new Stream($stream->get());
        $this->assertSame('hello', $stream->readString());

        // explicit offset: the length prefix is still read from the internal offset (and advances it by 4),
        // but the string content itself is read from the given offset instead
        $string = 'hello';
        $data = pack('V', strlen($string)) . 'PAD4' . $string; // length prefix, 4 padding bytes, then the string at byte 8
        $stream = new Stream($data);
        $this->assertSame('hello', $stream->readString(8));
    }

    public function testIsEnd(): void
    {
        $stream = new Stream('ab');

        $this->assertFalse($stream->isEnd());

        $stream->offset(2);
        $this->assertTrue($stream->isEnd());
    }

    public function testSize(): void
    {
        // default units=1
        $this->assertSame(1, Stream::size(Stream::TYPE_INT_8));
        $this->assertSame(2, Stream::size(Stream::TYPE_INT_16));
        $this->assertSame(4, Stream::size(Stream::TYPE_INT_32));
        $this->assertSame(8, Stream::size(Stream::TYPE_INT_64));
        $this->assertSame(1, Stream::size(Stream::TYPE_UINT_8));
        $this->assertSame(2, Stream::size(Stream::TYPE_UINT_16));
        $this->assertSame(4, Stream::size(Stream::TYPE_UINT_32));
        $this->assertSame(8, Stream::size(Stream::TYPE_UINT_64));

        // explicit units multiplies the per-unit size
        $this->assertSame(12, Stream::size(Stream::TYPE_INT_32, 3));

        // unrecognized type: multiplier of 0
        $this->assertSame(0, Stream::size(999));
    }
}
