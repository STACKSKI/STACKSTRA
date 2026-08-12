# STACKSTRA

Standalone, framework-agnostic PHP helper library: types, cache, filesystem, curl, console, streams and more.

- **Namespace:** `Stackstra\`
- **Requires:** PHP ^8.5
- **Install:** `composer require stackski/stackstra`

## Optional extensions

| Extension | Needed by |
|---|---|
| `ext-mbstring` | `Types\Chars` (multibyte string handling) |
| `ext-ctype` | `Types\Chars` (character-class checks) |
| `ext-curl` | `Curl\*` HTTP client subsystem |
| `ext-simplexml`, `ext-dom` | `Types\XML` |
| `ext-fileinfo` | `Filesystem\File` (MIME-type detection) |
| `ext-posix` | `Etc\Process` (Unix only) |

## Classes by domain

### Types: scalar & data-structure helpers
| Class | Purpose |
|---|---|
| `Types\Strings` | String manipulation: excerpts, trimming, case, search/replace, parsing, multibyte-safe slicing |
| `Types\Chars` | Multibyte-aware single-character operations on strings (nth char, random char, counts) |
| `Types\Boolean` | Boolean helpers (random bool, bool to/from string conversion) |
| `Types\Integer` | Integer helpers: random ints, int32/uint32 ranges, odd/even checks, unique random sets |
| `Types\Floats` | Float helpers: rounding, random floats, epsilon-safe comparisons |
| `Types\Hex` | Hex string conversions (to int, to binary, printability checks) |
| `Types\GUID` | GUID/UUID binary to/from hex conversions |
| `Types\Items` | Array element access: get-by-key-path, nth element/key, first/last slices |
| `Types\Objects` | Object introspection: to array, property access, emptiness checks |
| `Types\Resource` | PHP resource introspection (stream metadata, path) |
| `Types\XML` | XML to/from JSON/array/object conversions |

### Cache: in-memory value storage
| Class | Purpose |
|---|---|
| `Cache\Cache` | Core value cache with push/shift/get and first/last access |
| `Cache\CacheStack` | Bounded stack (LIFO-style) built on top of `Cache`, with a size limit |
| `Cache\CachePipe` | Bounded pipe (FIFO-style) wrapper around `Cache` |
| `Cache\CacheNested` | Safe by-reference pointer/get/set access into nested arrays |

### Curl: HTTP client subsystem
| Class | Purpose |
|---|---|
| `Curl\Curl` | High-level HTTP client entry point |
| `Curl\CurlOptions` | Typed cURL option builder/container |
| `Curl\CurlTask` | A single queued cURL request (handle, settings, lifecycle) |
| `Curl\CurlTasks` | Collection/queue of `CurlTask`s with completion/abort tracking |
| `Curl\CurlThrottle` | Rate-limits concurrent requests (slots, interval) |
| `Curl\CurlEvents` | Registers lifecycle callbacks (onSuccess, onComplete, ...) |
| `Curl\CurlEventArguments` | Argument object passed to `CurlEvents` callbacks |
| `Curl\CurlResponse` | Single HTTP response wrapper |
| `Curl\CurlResponseList` | Collection of `CurlResponse` objects |

### Filesystem: files & directories
| Class | Purpose |
|---|---|
| `Filesystem\File` | Static file operations: open/create/read/write/append |
| `Filesystem\FileObject` | Object-oriented wrapper around a single file |
| `Filesystem\Directory` | Static directory operations: create, list, change cwd, tmp dir |
| `Filesystem\DirectoryObject` | Iterable object-oriented wrapper around a directory |
| `Filesystem\Search` | Fluent file/directory search builder (path, pattern, type filters) |
| `Filesystem\Traits\HasPath` | Shared path-storage trait |
| `Filesystem\Traits\CanIterate` | Shared iteration trait for filesystem objects |
| `Filesystem\Traits\CanSearch` | Shared search trait for filesystem objects |

### Console: CLI tooling
| Class | Purpose |
|---|---|
| `Console\Console` | Console I/O helpers |
| `Console\ConsoleArguments` | Parses/queries CLI (`argv`) arguments |
| `Console\Shell` | Fluent shell command builder & runner |
| `Console\Prompt` | Interactive CLI menu/prompt with option callbacks |
| `Console\PromptItem` | A single selectable item within a `Prompt` |

### Etc: system, environment & web-request utilities
| Class | Purpose |
|---|---|
| `Etc\System` | OS/hostname/processor identification, endianness |
| `Etc\OS` | Operating-system detection helpers |
| `Etc\Hardware` | Hardware info (CPU core count) |
| `Etc\PHP` | Runtime process/memory info (pid, uid, gid, memory usage) |
| `Etc\Process` | OS process helpers (leader pid, existence check) |
| `Etc\Debug` | Enable/disable/query debug mode |
| `Etc\Timer` | Elapsed-time measurement/stopwatch |
| `Etc\Reflection` | Reflection helpers: constants, methods, short class names |
| `Etc\Defines` | Lookup/registration of PHP constants (`define()`) by prefix |
| `Etc\Nullptr` | Sentinel "no value" singleton, distinct from `null` |
| `Etc\Buffering` | Output buffering helpers (start/clean/end) |
| `Etc\Convert` | Time-unit conversions (seconds to/from ms/us/ns/minutes/hours/days) |
| `Etc\Date` | Date/time formatting and timezone helpers |
| `Etc\Session` | PHP session start/get/set/remove wrapper |
| `Etc\Cookies` | Cookie get/set/increment/decrement/delete helpers |
| `Etc\Headers` | HTTP response headers: status codes, JSON, file download, common responses |
| `Etc\HTML` | HTML escaping helper |
| `Etc\MIME` | MIME-type classification (isImage, isAudio, ...) |
| `Etc\Password` | Password hashing/verification wrapper |
| `Etc\Visitor` | Client-facing request info: IP, language detection |
| `Etc\ASCII` | ASCII index/char/hex conversions and printability checks |

### DateTime, Regexp, URL, Lang, Math, Lock, Cron, CSS, Stream, Map: misc domains
| Class | Purpose |
|---|---|
| `DateTime\DateTime` | Immutable-style timestamp wrapper with formatting helpers |
| `Regexp\Regexp` | Character-class "keep" filters (numeric, alpha, alphanumeric, ...) via regex |
| `URL\URL` | Parses/builds URL components (scheme, user, pass, host, path, ...) |
| `Lang\English` | English pluralization, singularization, and number-to-words |
| `Math\Math` | Number theory helpers: factorial, fibonacci, primality, primes, moving average |
| `Math\Crypt` | Cryptographic helpers: random bytes/numbers, password hashing, autologin tokens |
| `Lock\Lock` | Classic Unix lock-file implementation |
| `Cron\Cron` | Simple recurring task scheduler/runner |
| `CSS\Compressor` | CSS minifier |
| `Stream\Stream` | In-memory read/write stream wrapper |
| `Map\Map` | Fluent key/value map container |
| `INI\INI` | INI file/string parser |

### Exceptions & Singleton: cross-cutting
| Class | Purpose |
|---|---|
| `Exceptions\Exceptions` | Triggers errors/warnings/notices at configurable severity levels |
| `Singleton\Singleton` | Single-instance-per-file lock guard |
| `Traits\Singleton` | Singleton-instance trait for classes |

## Optional framework support

For Laravel projects, we've created a companion package: [stackski/stackstra-laravel](https://github.com/stackski/stackstra-laravel).

## Testing

```bash
composer install
vendor/bin/phpunit
```

Static analysis:

```bash
vendor/bin/phpstan analyse
```

## About

STACKSTRA is developed and maintained by [STACKSKI Inc.](https://stackski.com), an IT consulting and web development company, and gifted to the public as open source. Visit us at [stackski.com](https://stackski.com) (US) or [stackski.ca](https://stackski.ca) (Canada).

## License

MIT
