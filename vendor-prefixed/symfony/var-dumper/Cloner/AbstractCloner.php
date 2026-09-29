<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Cloner;

use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\Caster;
use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Exception\ThrowingCasterException;

/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements ClonerInterface
{
    public static array $defaultCasters = [
        '__PHP_Incomplete_Class' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\Caster', 'castPhpIncompleteClass'],

        'AddressInfo' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\AddressInfoCaster', 'castAddressInfo'],
        'Socket' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SocketCaster', 'castSocket'],

        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\CutStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\CutArrayStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castCutArray'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ConstStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\EnumStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castEnum'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ScalarStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castScalar'],

        'Fiber' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\FiberCaster', 'castFiber'],

        'Closure' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClosure'],
        'Generator' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castGenerator'],
        'ReflectionType' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castType'],
        'ReflectionAttribute' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castAttribute'],
        'ReflectionGenerator' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReflectionGenerator'],
        'ReflectionClass' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClass'],
        'ReflectionClassConstant' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClassConstant'],
        'ReflectionFunctionAbstract' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castFunctionAbstract'],
        'ReflectionMethod' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castMethod'],
        'ReflectionParameter' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castParameter'],
        'ReflectionProperty' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castProperty'],
        'ReflectionReference' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReference'],
        'ReflectionExtension' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castExtension'],
        'ReflectionZendExtension' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castZendExtension'],

        'Doctrine\Common\Persistence\ObjectManager' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Doctrine\Common\Proxy\Proxy' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castCommonProxy'],
        'Doctrine\ORM\Proxy\Proxy' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castOrmProxy'],
        'Doctrine\ORM\PersistentCollection' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castPersistentCollection'],
        'Doctrine\Persistence\ObjectManager' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],

        'DOMException' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castException'],
        'Dom\Exception' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castException'],
        'DOMStringList' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'DOMNameList' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'DOMImplementation' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'Dom\Implementation' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'DOMImplementationList' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'DOMNode' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'Dom\Node' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'DOMNameSpaceNode' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'DOMDocument' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocument'],
        'Dom\XMLDocument' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castXMLDocument'],
        'Dom\HTMLDocument' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castHTMLDocument'],
        'DOMNodeList' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'Dom\NodeList' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'DOMNamedNodeMap' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'Dom\DTDNamedNodeMap' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'DOMXPath' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'Dom\XPath' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'Dom\HTMLCollection' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],
        'Dom\TokenList' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDom'],

        'XMLReader' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\XmlReaderCaster', 'castXmlReader'],

        'ErrorException' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castErrorException'],
        'Exception' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castException'],
        'Error' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castError'],
        'Symfony\Bridge\Monolog\Logger' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\DependencyInjection\ContainerInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\EventDispatcher\EventDispatcherInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\HttpClient\AmpHttpClient' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\CurlHttpClient' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\NativeHttpClient' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\Response\AmpResponse' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpClient\Response\AmpResponseV4' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpClient\Response\AmpResponseV5' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpClient\Response\CurlResponse' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpClient\Response\NativeResponse' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpFoundation\Request' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castRequest'],
        'Symfony\Component\Uid\Ulid' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUlid'],
        'Symfony\Component\Uid\Uuid' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUuid'],
        'Symfony\Component\VarExporter\Internal\LazyObjectState' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castLazyObjectState'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Exception\ThrowingCasterException' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castThrowingCasterException'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\TraceStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castTraceStub'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\FrameStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castFrameStub'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Cloner\AbstractCloner' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\ErrorHandler\Exception\FlattenException' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castFlattenException'],
        'Symfony\Component\ErrorHandler\Exception\SilencedErrorContext' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castSilencedErrorContext'],

        'Imagine\Image\ImageInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ImagineCaster', 'castImage'],

        'Ramsey\Uuid\UuidInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\UuidCaster', 'castRamseyUuid'],

        'ProxyManager\Proxy\ProxyInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ProxyManagerCaster', 'castProxy'],
        'PHPUnit_Framework_MockObject_MockObject' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\MockObject' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\Stub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Prophecy\Prophecy\ProphecySubjectInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Mockery\MockInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],

        'PDO' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdo'],
        'PDOStatement' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdoStatement'],

        'AMQPConnection' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castConnection'],
        'AMQPChannel' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castChannel'],
        'AMQPQueue' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castQueue'],
        'AMQPExchange' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castExchange'],
        'AMQPEnvelope' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castEnvelope'],

        'ArrayObject' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayObject'],
        'ArrayIterator' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayIterator'],
        'SplDoublyLinkedList' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castDoublyLinkedList'],
        'SplFileInfo' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileInfo'],
        'SplFileObject' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileObject'],
        'SplHeap' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'SplObjectStorage' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castObjectStorage'],
        'SplPriorityQueue' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'OuterIterator' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castOuterIterator'],
        'WeakMap' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castWeakMap'],
        'WeakReference' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castWeakReference'],

        'Redis' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedis'],
        'Relay\Relay' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedis'],
        'RedisArray' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisArray'],
        'RedisCluster' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisCluster'],

        'DateTimeInterface' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castDateTime'],
        'DateInterval' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castInterval'],
        'DateTimeZone' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castTimeZone'],
        'DatePeriod' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castPeriod'],

        'GMP' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\GmpCaster', 'castGmp'],

        'MessageFormatter' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castMessageFormatter'],
        'NumberFormatter' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castNumberFormatter'],
        'IntlTimeZone' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlTimeZone'],
        'IntlCalendar' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlCalendar'],
        'IntlDateFormatter' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlDateFormatter'],

        'Memcached' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\MemcachedCaster', 'castMemcached'],

        'Ds\Collection' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castCollection'],
        'Ds\Map' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castMap'],
        'Ds\Pair' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castPair'],
        'RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DsPairStub' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castPairStub'],

        'mysqli_driver' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\MysqliCaster', 'castMysqliDriver'],

        'CurlHandle' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\CurlCaster', 'castCurl'],

        'Dba\Connection' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':dba' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':dba persistent' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],

        'GdImage' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\GdCaster', 'castGd'],

        'SQLite3Result' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\SqliteCaster', 'castSqlite3Result'],

        'PgSql\Lob' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLargeObject'],
        'PgSql\Connection' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'],
        'PgSql\Result' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castResult'],

        ':process' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castProcess'],
        ':stream' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],

        'OpenSSLAsymmetricKey' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\OpenSSLCaster', 'castOpensslAsymmetricKey'],
        'OpenSSLCertificateSigningRequest' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\OpenSSLCaster', 'castOpensslCsr'],
        'OpenSSLCertificate' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\OpenSSLCaster', 'castOpensslX509'],

        ':persistent stream' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':stream-context' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStreamContext'],

        'XmlParser' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'],

        'RdKafka' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castRdKafka'],
        'RdKafka\Conf' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castConf'],
        'RdKafka\KafkaConsumer' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castKafkaConsumer'],
        'RdKafka\Metadata\Broker' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castBrokerMetadata'],
        'RdKafka\Metadata\Collection' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castCollectionMetadata'],
        'RdKafka\Metadata\Partition' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castPartitionMetadata'],
        'RdKafka\Metadata\Topic' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicMetadata'],
        'RdKafka\Message' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castMessage'],
        'RdKafka\Topic' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopic'],
        'RdKafka\TopicPartition' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicPartition'],
        'RdKafka\TopicConf' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicConf'],

        'FFI\CData' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\FFICaster', 'castCTypeOrCData'],
        'FFI\CType' => ['RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Caster\FFICaster', 'castCTypeOrCData'],
    ];

    protected int $maxItems = 2500;
    protected int $maxString = -1;
    protected int $minDepth = 1;

    /**
     * @var array<string, list<callable>>
     */
    private array $casters = [];

    /**
     * @var callable|null
     */
    private $prevErrorHandler;

    private array $classInfo = [];
    private int $filter = 0;

    /**
     * @param callable[]|null $casters A map of casters
     *
     * @see addCasters
     */
    public function __construct(?array $casters = null)
    {
        $this->addCasters($casters ?? static::$defaultCasters);
    }

    /**
     * Adds casters for resources and objects.
     *
     * Maps resources or object types to a callback.
     * Use types as keys and callable casters as values.
     * Prefix types with `::`,
     * see e.g. self::$defaultCasters.
     *
     * @param array<string, callable> $casters A map of casters
     */
    public function addCasters(array $casters): void
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
    }

    /**
     * Adds default casters for resources and objects.
     *
     * Maps resources or object types to a callback.
     * Use types as keys and callable casters as values.
     * Prefix types with `::`,
     * see e.g. self::$defaultCasters.
     *
     * @param array<string, callable> $casters A map of casters
     */
    public static function addDefaultCasters(array $casters): void
    {
        self::$defaultCasters = [...self::$defaultCasters, ...$casters];
    }

    /**
     * Sets the maximum number of items to clone past the minimum depth in nested structures.
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    /**
     * Sets the maximum cloned length for strings.
     */
    public function setMaxString(int $maxString): void
    {
        $this->maxString = $maxString;
    }

    /**
     * Sets the minimum tree depth where we are guaranteed to clone all the items.  After this
     * depth is reached, only setMaxItems items will be cloned.
     */
    public function setMinDepth(int $minDepth): void
    {
        $this->minDepth = $minDepth;
    }

    /**
     * Clones a PHP variable.
     *
     * @param int $filter A bit field of Caster::EXCLUDE_* constants
     */
    public function cloneVar(mixed $var, int $filter = 0): Data
    {
        $this->prevErrorHandler = set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (\E_RECOVERABLE_ERROR === $type || \E_USER_ERROR === $type) {
                // Cloner never dies
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }

            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }

            return false;
        });
        $this->filter = $filter;

        if ($gc = gc_enabled()) {
            gc_disable();
        }
        try {
            return new Data($this->doClone($var));
        } finally {
            if ($gc) {
                gc_enable();
            }
            restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }

    /**
     * Effectively clones the PHP variable.
     */
    abstract protected function doClone(mixed $var): array;

    /**
     * Casts an object to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     */
    protected function castObject(Stub $stub, bool $isNested): array
    {
        $obj = $stub->value;
        $class = $stub->class;

        if (str_contains($class, "@anonymous\0")) {
            $stub->class = get_debug_type($obj);
        }
        if (isset($this->classInfo[$class])) {
            [$i, $parents, $hasDebugInfo, $fileInfo] = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = method_exists($class, '__debugInfo');

            foreach (class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';

            $r = new \ReflectionClass($class);
            $fileInfo = $r->isInternal() || $r->isSubclassOf(Stub::class) ? [] : [
                'file' => $r->getFileName(),
                'line' => $r->getStartLine(),
            ];

            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }

        $stub->attr += $fileInfo;
        $a = Caster::castObject($obj, $class, $hasDebugInfo, $stub->class);

        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }

    /**
     * Casts a resource to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     */
    protected function castResource(Stub $stub, bool $isNested): array
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;

        try {
            if (!empty($this->casters[':'.$type])) {
                foreach ($this->casters[':'.$type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }
}
