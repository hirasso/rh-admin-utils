<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by hirasso on 22-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Symfony\Component\VarDumper\Cloner;

use RH\AdminUtils\Symfony\Component\VarDumper\Caster\Caster;
use RH\AdminUtils\Symfony\Component\VarDumper\Exception\ThrowingCasterException;

/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements ClonerInterface
{
    public static array $defaultCasters = [
        '__PHP_Incomplete_Class' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\Caster', 'castPhpIncompleteClass'],

        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\CutStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\CutArrayStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'castCutArray'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\ConstStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\EnumStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'castEnum'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\ScalarStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'castScalar'],

        'Fiber' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\FiberCaster', 'castFiber'],

        'Closure' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClosure'],
        'Generator' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castGenerator'],
        'ReflectionType' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castType'],
        'ReflectionAttribute' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castAttribute'],
        'ReflectionGenerator' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReflectionGenerator'],
        'ReflectionClass' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClass'],
        'ReflectionClassConstant' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClassConstant'],
        'ReflectionFunctionAbstract' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castFunctionAbstract'],
        'ReflectionMethod' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castMethod'],
        'ReflectionParameter' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castParameter'],
        'ReflectionProperty' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castProperty'],
        'ReflectionReference' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReference'],
        'ReflectionExtension' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castExtension'],
        'ReflectionZendExtension' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castZendExtension'],

        'Doctrine\Common\Persistence\ObjectManager' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Doctrine\Common\Proxy\Proxy' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castCommonProxy'],
        'Doctrine\ORM\Proxy\Proxy' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castOrmProxy'],
        'Doctrine\ORM\PersistentCollection' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castPersistentCollection'],
        'Doctrine\Persistence\ObjectManager' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],

        'DOMException' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castException'],
        'Dom\Exception' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castException'],
        'DOMStringList' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNameList' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMImplementation' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'Dom\Implementation' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'DOMImplementationList' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNode' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNode'],
        'Dom\Node' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNode'],
        'DOMNameSpaceNode' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNameSpaceNode'],
        'DOMDocument' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocument'],
        'Dom\XMLDocument' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castXMLDocument'],
        'Dom\HTMLDocument' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castHTMLDocument'],
        'DOMNodeList' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'Dom\NodeList' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNamedNodeMap' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'Dom\DTDNamedNodeMap' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMCharacterData' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castCharacterData'],
        'Dom\CharacterData' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castCharacterData'],
        'DOMAttr' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castAttr'],
        'Dom\Attr' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castAttr'],
        'DOMElement' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castElement'],
        'Dom\Element' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castElement'],
        'DOMText' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castText'],
        'Dom\Text' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castText'],
        'DOMDocumentType' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocumentType'],
        'Dom\DocumentType' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocumentType'],
        'DOMNotation' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNotation'],
        'Dom\Notation' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNotation'],
        'DOMEntity' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castEntity'],
        'Dom\Entity' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castEntity'],
        'DOMProcessingInstruction' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castProcessingInstruction'],
        'Dom\ProcessingInstruction' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castProcessingInstruction'],
        'DOMXPath' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DOMCaster', 'castXPath'],

        'XMLReader' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\XmlReaderCaster', 'castXmlReader'],

        'ErrorException' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castErrorException'],
        'Exception' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castException'],
        'Error' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castError'],
        'Symfony\Bridge\Monolog\Logger' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\DependencyInjection\ContainerInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\EventDispatcher\EventDispatcherInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\HttpClient\AmpHttpClient' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\CurlHttpClient' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\NativeHttpClient' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\Response\AmpResponse' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpClient\Response\CurlResponse' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpClient\Response\NativeResponse' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Symfony\Component\HttpFoundation\Request' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castRequest'],
        'Symfony\Component\Uid\Ulid' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUlid'],
        'Symfony\Component\Uid\Uuid' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUuid'],
        'Symfony\Component\VarExporter\Internal\LazyObjectState' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castLazyObjectState'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Exception\ThrowingCasterException' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castThrowingCasterException'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\TraceStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castTraceStub'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\FrameStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castFrameStub'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Cloner\AbstractCloner' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\ErrorHandler\Exception\FlattenException' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castFlattenException'],
        'Symfony\Component\ErrorHandler\Exception\SilencedErrorContext' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castSilencedErrorContext'],

        'Imagine\Image\ImageInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ImagineCaster', 'castImage'],

        'Ramsey\Uuid\UuidInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\UuidCaster', 'castRamseyUuid'],

        'ProxyManager\Proxy\ProxyInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ProxyManagerCaster', 'castProxy'],
        'PHPUnit_Framework_MockObject_MockObject' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\MockObject' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\Stub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Prophecy\Prophecy\ProphecySubjectInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Mockery\MockInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],

        'PDO' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdo'],
        'PDOStatement' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdoStatement'],

        'AMQPConnection' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castConnection'],
        'AMQPChannel' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castChannel'],
        'AMQPQueue' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castQueue'],
        'AMQPExchange' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castExchange'],
        'AMQPEnvelope' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castEnvelope'],

        'ArrayObject' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayObject'],
        'ArrayIterator' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayIterator'],
        'SplDoublyLinkedList' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castDoublyLinkedList'],
        'SplFileInfo' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileInfo'],
        'SplFileObject' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileObject'],
        'SplHeap' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'SplObjectStorage' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castObjectStorage'],
        'SplPriorityQueue' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'OuterIterator' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castOuterIterator'],
        'WeakMap' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castWeakMap'],
        'WeakReference' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\SplCaster', 'castWeakReference'],

        'Redis' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedis'],
        'Relay\Relay' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedis'],
        'RedisArray' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisArray'],
        'RedisCluster' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisCluster'],

        'DateTimeInterface' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DateCaster', 'castDateTime'],
        'DateInterval' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DateCaster', 'castInterval'],
        'DateTimeZone' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DateCaster', 'castTimeZone'],
        'DatePeriod' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DateCaster', 'castPeriod'],

        'GMP' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\GmpCaster', 'castGmp'],

        'MessageFormatter' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\IntlCaster', 'castMessageFormatter'],
        'NumberFormatter' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\IntlCaster', 'castNumberFormatter'],
        'IntlTimeZone' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlTimeZone'],
        'IntlCalendar' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlCalendar'],
        'IntlDateFormatter' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlDateFormatter'],

        'Memcached' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\MemcachedCaster', 'castMemcached'],

        'Ds\Collection' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DsCaster', 'castCollection'],
        'Ds\Map' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DsCaster', 'castMap'],
        'Ds\Pair' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DsCaster', 'castPair'],
        'RH\AdminUtils\Symfony\Component\VarDumper\Caster\DsPairStub' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\DsCaster', 'castPairStub'],

        'mysqli_driver' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\MysqliCaster', 'castMysqliDriver'],

        'CurlHandle' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castCurl'],

        ':dba' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':dba persistent' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],

        'GdImage' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castGd'],
        ':gd' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castGd'],

        ':pgsql large object' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLargeObject'],
        ':pgsql link' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql link persistent' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql result' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castResult'],
        ':process' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castProcess'],
        ':stream' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],

        'OpenSSLCertificate' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castOpensslX509'],
        ':OpenSSL X.509' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castOpensslX509'],

        ':persistent stream' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':stream-context' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStreamContext'],

        'XmlParser' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'],
        ':xml' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'],

        'RdKafka' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castRdKafka'],
        'RdKafka\Conf' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castConf'],
        'RdKafka\KafkaConsumer' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castKafkaConsumer'],
        'RdKafka\Metadata\Broker' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castBrokerMetadata'],
        'RdKafka\Metadata\Collection' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castCollectionMetadata'],
        'RdKafka\Metadata\Partition' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castPartitionMetadata'],
        'RdKafka\Metadata\Topic' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicMetadata'],
        'RdKafka\Message' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castMessage'],
        'RdKafka\Topic' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopic'],
        'RdKafka\TopicPartition' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicPartition'],
        'RdKafka\TopicConf' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicConf'],

        'FFI\CData' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\FFICaster', 'castCTypeOrCData'],
        'FFI\CType' => ['RH\AdminUtils\Symfony\Component\VarDumper\Caster\FFICaster', 'castCTypeOrCData'],
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
     * Maps resources or objects types to a callback.
     * Types are in the key, with a callable caster for value.
     * Resource types are to be prefixed with a `:`,
     * see e.g. static::$defaultCasters.
     *
     * @param callable[] $casters A map of casters
     */
    public function addCasters(array $casters): void
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
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
