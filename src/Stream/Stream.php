<?php

namespace Stackstra\Stream;

use Stackstra\Types\Items;
use Stackstra\Types\Strings;

use const Stackstra\MASK_1;
use const Stackstra\MASK_2;
use const Stackstra\MASK_3;
use const Stackstra\MASK_4;
use const Stackstra\MASK_5;
use const Stackstra\MASK_6;
use const Stackstra\MASK_7;
use const Stackstra\MASK_8;

class Stream
{
	protected ?string $data      = null;
	protected     int $data_size = 0;
	protected     int $offset    = 0;

	protected static ?array $chars = null;


	# https://www.php.net/manual/en/function.pack.php

	const int TYPE_INT_8   = MASK_1;
	const int TYPE_INT_16  = MASK_2;
	const int TYPE_INT_32  = MASK_3;
	const int TYPE_INT_64  = MASK_4;

	const int TYPE_UINT_8  = MASK_5;
	const int TYPE_UINT_16 = MASK_6;
	const int TYPE_UINT_32 = MASK_7;
	const int TYPE_UINT_64 = MASK_8;

	const array CHARS_LITTLE_ENDIAN =
	[
		self::TYPE_INT_8  => 'c*',
		self::TYPE_INT_16 => 's*',
		self::TYPE_INT_32 => 'l*',
		self::TYPE_INT_64 => 'q*',

		self::TYPE_UINT_8  => 'C*',
		self::TYPE_UINT_16 => 'v*',
		self::TYPE_UINT_32 => 'V*',
		self::TYPE_UINT_64 => 'P*'
	];

	const array CHARS_BIG_ENDIAN =
	[
		self::TYPE_INT_8  => 'c*',
		self::TYPE_INT_16 => 's*',
		self::TYPE_INT_32 => 'l*',
		self::TYPE_INT_64 => 'q*',

		self::TYPE_UINT_8  => 'C*',
		self::TYPE_UINT_16 => 'n*',
		self::TYPE_UINT_32 => 'N*',
		self::TYPE_UINT_64 => 'J*'
	];

	const int TYPE_INT_8_SIZE  = 1;
	const int TYPE_INT_8_MIN   = -128;
	const int TYPE_INT_8_MAX   =  127;

	const int TYPE_INT_16_SIZE = 2;
	const int TYPE_INT_16_MIN  = -32768;
	const int TYPE_INT_16_MAX  =  32767;


	const int TYPE_INT_32_SIZE = 4;
	const int TYPE_INT_32_MIN  = -2147483648;
	const int TYPE_INT_32_MAX  =  2147483647;


	const int TYPE_INT_64_SIZE = 8;
	const int TYPE_INT_64_MIN  = PHP_INT_MIN;
	const int TYPE_INT_64_MAX  = PHP_INT_MAX;


	const int TYPE_UINT_8_MIN = 0;
	const int TYPE_UINT_8_MAX = 255;

	const int TYPE_UINT_16_MIN = 0;
	const int TYPE_UINT_16_MAX = 65535;

	const int TYPE_UINT_32_MIN = 0;
	const int TYPE_UINT_32_MAX = 4294967295;

	const int    TYPE_UINT_64_MIN = 0;
	const string TYPE_UINT_64_MAX = "18446744073709551615"; # we are unable to represent this value as PHP int value (PHP's int is a signed 64bit number)


	public function __construct(?string $data = null, ?int $data_size = null)
	{
		static::set($data, $data_size);

		self::initialize();
	}

	public static function make(?string $data = null, ?int $data_size = null): static
	{
		return new static(data: $data, data_size: $data_size);
	}

	public static function initialize()
	{
		if (self::$chars === null)
		{
			     if (APP_BYTE_ORDER_IS_LITTLE_ENDIAN) { self::$chars = static::CHARS_LITTLE_ENDIAN; }
			else                                      { self::$chars = static::CHARS_BIG_ENDIAN;    }
		}
	}

	public function set(?string $data = null, ?int $data_size = 0)
	{
		if ($data_size === null)
		{
			     if ($data === null) { $data_size = 0; }
			else                     { $data_size = Strings::size($data); }
		}

		$this->data      = $data;
		$this->data_size = $data_size;

		$this->offset = 0;
	}

	public function offset(?int $offset = null): int
	{
		if ($offset !== null)
		{
			$this->offset = (int) $offset;
		}

		return $this->offset;
	}

	public function length(): int
	{
		return $this->data_size;
	}

	public function get(): ?string
	{
		return $this->data;
	}

	public function getChunked(int $chunk_size = 1): array
	{
		if (!$this->data)
		{
			return [];
		}

		return str_split($this->data, $chunk_size);
	}

	public function reset(?string $data = null)
	{
		$this->data      = $data;
		$this->data_size = 0;

		$this->offset = 0;
	}

	public function pack(int $type, array|string $data): ?string
	{
		     if (is_array($data)) { $result = pack(self::$chars[$type], ...$data); }
		else                      { $result = pack(self::$chars[$type],    $data); }

		if ($result === false)
		{
			return null;
		}

		return $result;
	}

	public function unpack(int $type, ?int $length = null, ?int $offset = null, ?int $units = null)
	{
		if ($offset === null)
		{
			$offset = $this->offset;
		}

		if ($length === null and $units !== null)
		{
			$length = static::size($type, $units);
		}

		     if ($offset or $length) { $data = substr($this->data, $offset, $length); }
		else                         { $data = $this->data; }

		     if ($length === null) { $this->offset = $this->data_size; }
		else                       { $this->offset += $length;         }

		$result = unpack(self::$chars[$type], $data);

		if ($units === 1)
		{
			return reset($result);
		}

		return $result;
	}

	public static function encode(int $type, array|string $data): ?string
	{
		     if (is_array($data)) { return pack(self::$chars[$type], ...$data); }
		else                      { return pack(self::$chars[$type],    $data); }
	}

	public static function decode(int $type, string $data): ?array
	{
		     if (APP_BYTE_ORDER_IS_LITTLE_ENDIAN) { return unpack(static::CHARS_LITTLE_ENDIAN[$type], $data); }
		else                                      { return unpack(static::CHARS_BIG_ENDIAN   [$type], $data); }
	}

	public static function encodeInt8  (array|string $data): ?string { return static::encode(self::TYPE_INT_8,   $data); }
	public static function encodeInt16 (array|string $data): ?string { return static::encode(self::TYPE_INT_16,  $data); }
	public static function encodeInt32 (array|string $data): ?string { return static::encode(self::TYPE_INT_32,  $data); }
	public static function encodeInt64 (array|string $data): ?string { return static::encode(self::TYPE_INT_64,  $data); }

	public static function encodeUint8 (array|string $data): ?string { return static::encode(self::TYPE_UINT_8,  $data); }
	public static function encodeUint16(array|string $data): ?string { return static::encode(self::TYPE_UINT_16, $data); }
	public static function encodeUint32(array|string $data): ?string { return static::encode(self::TYPE_UINT_32, $data); }
	public static function encodeUint64(array|string $data): ?string { return static::encode(self::TYPE_UINT_64, $data); }

	public static function decodeInt8  (array|string $data): ?array  { return static::decode(self::TYPE_INT_8,   $data); }
	public static function decodeInt16 (array|string $data): ?array  { return static::decode(self::TYPE_INT_16,  $data); }
	public static function decodeInt32 (array|string $data): ?array  { return static::decode(self::TYPE_INT_32,  $data); }
	public static function decodeInt64 (array|string $data): ?array  { return static::decode(self::TYPE_INT_64,  $data); }

	public static function decodeUint8 (array|string $data): ?array  { return static::decode(self::TYPE_UINT_8,  $data); }
	public static function decodeUint16(array|string $data): ?array  { return static::decode(self::TYPE_UINT_16, $data); }
	public static function decodeUint32(array|string $data): ?array  { return static::decode(self::TYPE_UINT_32, $data); }
	public static function decodeUint64(array|string $data): ?array  { return static::decode(self::TYPE_UINT_64, $data); }

	public function write(int $type, array|string $data): self { $this->data .= static::pack($type, $data); return $this; }

	public function writeInt8  (array|string|int $number): self { $this->data .= static::pack(self::TYPE_INT_8,   $number); return $this; }
	public function writeInt16 (array|string|int $number): self { $this->data .= static::pack(self::TYPE_INT_16,  $number); return $this; }
	public function writeInt32 (array|string|int $number): self { $this->data .= static::pack(self::TYPE_INT_32,  $number); return $this; }
	public function writeInt64 (array|string|int $number): self { $this->data .= static::pack(self::TYPE_INT_64,  $number); return $this; }

	public function writeUint8 (array|string|int $number): self { $this->data .= static::pack(self::TYPE_UINT_8,  $number); return $this; }
	public function writeUint16(array|string|int $number): self { $this->data .= static::pack(self::TYPE_UINT_16, $number); return $this; }
	public function writeUint32(array|string|int $number): self { $this->data .= static::pack(self::TYPE_UINT_32, $number); return $this; }
	public function writeUint64(array|string|int $number): self { $this->data .= static::pack(self::TYPE_UINT_64, $number); return $this; }

	protected function writeArrayChunk(int $int_type, array $array, int $array_length)
	{
		$this->writeUint32($array_length);

		return static::pack($int_type, $array);
	}

	protected function writeArray(array $array, int $int_type, bool $save_keys, bool $save_values)
	{
		# TODO: test and fix

		$length = count($array);

		if ($save_keys)   { $this->data .= $this->writeArrayChunk($int_type,   array_keys($array), $length); }
		if ($save_values) { $this->data .= $this->writeArrayChunk($int_type, array_values($array), $length); }
	}

	public function writeArrayInt8 (array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_INT_8,  $save_keys, $save_values); return $this; }
	public function writeArrayInt16(array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_INT_16, $save_keys, $save_values); return $this; }
	public function writeArrayInt32(array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_INT_32, $save_keys, $save_values); return $this; }
	public function writeArrayInt64(array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_INT_64, $save_keys, $save_values); return $this; }

	public function writeArrayUint8 (array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_UINT_8,  $save_keys, $save_values); return $this; }
	public function writeArrayUint16(array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_UINT_16, $save_keys, $save_values); return $this; }
	public function writeArrayUint32(array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_UINT_32, $save_keys, $save_values); return $this; }
	public function writeArrayUint64(array $array, bool $save_keys = true, bool $save_values = true): self { $this->writeArray($array, self::TYPE_UINT_64, $save_keys, $save_values); return $this; }

	public function writeNull()
	{
		$this->data .= "\0";
	}

	public function writeString(string $string, $length = null)
	{
		if ($length === null)
		{
			$length = Strings::size($string);
		}

		self::writeUint32($length);

		$this->data .= $string;
	}

	public function read(int $type, ?int $units = null, ?int $offset = null, ?int $length = null)
	{
		return static::unpack($type, $length, $offset, $units);
	}

	public function readInt8  (?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_INT_8,   $units,                          $offset, $units); }
	public function readInt16 (?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_INT_16,  $units * self::TYPE_INT_16_SIZE, $offset, $units); }
	public function readInt32 (?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_INT_32,  $units * self::TYPE_INT_32_SIZE, $offset, $units); }
	public function readInt64 (?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_INT_64,  $units * self::TYPE_INT_64_SIZE, $offset, $units); }

	public function readUint8 (?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_UINT_8,  $units,                          $offset, $units); }
	public function readUint16(?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_UINT_16, $units * self::TYPE_INT_16_SIZE, $offset, $units); }
	public function readUint32(?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_UINT_32, $units * self::TYPE_INT_32_SIZE, $offset, $units); }
	public function readUint64(?int $units = null, ?int $offset = null) { return static::unpack(self::TYPE_UINT_64, $units * self::TYPE_INT_64_SIZE, $offset, $units); }

	protected function readArrayChunk(int $int_type)
	{
		$length = $this->readUint32(1);

		return static::unpack($int_type, self::size($int_type, $length), null);
	}

	protected function readArray(int $int_type, bool $read_keys, bool $read_values)
	{
		if ($read_keys and $read_values)
		{
			$keys   = $this->readArrayChunk($int_type);
			$values = $this->readArrayChunk($int_type);

			return array_combine($keys, $values);
		}

		if ($read_keys)
		{
			$keys = $this->readArrayChunk($int_type);
			$keys_length = count($keys);

			$values = Items::repeat(null, $keys_length);

			return array_combine($keys, $values);
		}

		if ($read_values)
		{
			return $this->readArrayChunk($int_type);
		}

		return [];
	}

	public function readArrayInt8 (bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_INT_8,  $read_keys, $read_values); }
	public function readArrayInt16(bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_INT_16, $read_keys, $read_values); }
	public function readArrayInt32(bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_INT_32, $read_keys, $read_values); }
	public function readArrayInt64(bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_INT_64, $read_keys, $read_values); }

	public function readArrayUint8 (bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_UINT_8,  $read_keys, $read_values); }
	public function readArrayUint16(bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_UINT_16, $read_keys, $read_values); }
	public function readArrayUint32(bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_UINT_32, $read_keys, $read_values); }
	public function readArrayUint64(bool $read_keys = true, bool $read_values = true) { return $this->readArray(self::TYPE_UINT_64, $read_keys, $read_values); }

	public function readString($offset = null)
	{
		$length = $this->readUint32(1);

		if ($offset === null)
		{
			$offset = $this->offset;
		}

		$this->offset += $length;

		return substr($this->data, $offset, $length);
	}

	//protected function readArrayIntegers(int $int_type)
	//{
	//	$array_length = $this->readUint32(1);
	//
	//	return $this->readUint32($array_length);
	//
	//	$int_size = self::sizeInt($int_type);
	//
	//	$this->data .= self::decodeInt($array, $int_type);
	//
	//	$this->offset += $array_length * $int_size;
	//}
	//
	//public function readArrayValuesUint32(array $array)
	//{
	//	$values = array_values($array);
	//
	//	$this->writeArrayIntegers($values, self::TYPE_INT_32_SIZE);
	//}
	//
	//public function readArrayKeysUint32(array $array)
	//{
	//	$keys = array_keys($array);
	//
	//	$this->writeArrayIntegers($keys, self::TYPE_INT_32_SIZE);
	//}
	//
	//public function readArrayUint32(array $array)
	//{
	//	$this->writeArrayKeysUint32($array);
	//	$this->writeArrayValuesUint32($array);
	//}

	public function isEnd(): bool
	{
		return $this->offset >= $this->data_size;
	}

	public static function size(int $type, int $units = 1): int
	{
		$multiplier = match ($type)
		{
			self::TYPE_INT_8,  self::TYPE_UINT_8  => 1,
			self::TYPE_INT_16, self::TYPE_UINT_16 => 2,
			self::TYPE_INT_32, self::TYPE_UINT_32 => 4,
			self::TYPE_INT_64, self::TYPE_UINT_64 => 8,

			default => 0
		};

		return $units * $multiplier;
	}
}
