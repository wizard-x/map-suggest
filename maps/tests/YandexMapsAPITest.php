<?php declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Tools\Interfaces\GeoSearchInterface;

use App\Tools\Yandex\API\YandexMapsAPI;
use App\Tools\Yandex\API\MapsAPIResponseTransformer;
use App\Tools\Yandex\Exceptions\YandexMapsAPIException;
use App\Tools\DTO\MapsDataDTO;
use App\Repository\Redis\MapPointRedisRepository;


class YandexMapsAPITest extends KernelTestCase {

    public function setUp(): void {
        $kernel = self::bootKernel([
            'debug' => false,
        ]);
    }


    public function prerequisites(): array {
        $redis_service = self::$container->get('snc_redis.default');
        $storage = new MapPointRedisRepository($redis_service);
        $transformer = new MapsAPIResponseTransformer();
        $yandexMapsApi = new YandexMapsAPI(getenv('YANDEX_MAPS_API_TOKEN'), $transformer, $storage);
        return ['maps' => $yandexMapsApi, 'transformer' => $transformer];
    }


    public function test_maps_data_dto(): void {
        $dto = new MapsDataDTO();
        $dto
            ->setRequest('sample')
            ->setData([1,2,3])
        ;
        $this->assertSame(
            '{"request":"sample","data":[1,2,3]}',
            $dto->toJSON()
        );
    }


    public function test_is_class_available(): void {
        // php v7.1+
        list('maps' => $yandexMapsApi, 'transformer' => $transformer) = $this->prerequisites();
        $this->assertInstanceOf(GeoSearchInterface::class, $yandexMapsApi);
    }


    public function test_parse_filter(): void {
        $transformer = new MapsAPIResponseTransformer();
        $yandexMapsApi = $this->createStub(YandexMapsAPI::class);

        $this->callProtectedMethod($yandexMapsApi, 'parseFilter', ['string filter']);
        $this->assertTrue(true);

        $this->callProtectedMethod($yandexMapsApi, 'parseFilter', [[1,2]]);
        $this->assertTrue(true);

        try {
            $this->callProtectedMethod($yandexMapsApi, 'parseFilter', [112]);
            $this->fail('Expected exception is not thrown for "112"');
        } catch (YandexMapsAPIException $e) {
            $this->assertTrue(true);
        }

        try {
            $this->callProtectedMethod($yandexMapsApi, 'parseFilter', [1.54]);
            $this->fail('Expected exception is not thrown for "1.54"');
        } catch (YandexMapsAPIException $e) {
            $this->assertTrue(true);
        }
    }


    public function callProtectedMethod(object $object, string $method, array $args) {
        $classname = get_class($object);
        $refObject = new \ReflectionClass($classname);
        $protectedMethod = $refObject->getMethod($method);
        $protectedMethod->setAccessible(true);
        return $protectedMethod->invokeArgs($object, $args);
    }


    public function test_transformer_result(): void {
        $transformer = new MapsAPIResponseTransformer();
        $response = json_decode($this->get_fake_search_data(), true);
        $transformed = $transformer->transform($response);

        $location = $transformed->getData()[0];
        $this->assertCount(2, $location);
        $this->assertArrayHasKey('text', $location);
        $this->assertIsString($location['text']);

        $this->assertArrayHasKey('point', $location);
        $this->assertCount(2, $location['point']);

        foreach ($location['point'] as $coord) {
            $this->assertIsNumeric($coord);
        }
    }


    public function test_real_search_results(): void {
        list('maps' => $yandexMapsApi, 'transformer' => $transformer) = $this->prerequisites();

        $response = $yandexMapsApi->search('моск');
        $this->assertInstanceOf(MapsDataDTO::class, $response);
    }


    public function get_fake_search_data(): string {
        return '{"response":{"GeoObjectCollection":{"metaDataProperty":{"GeocoderResponseMetaData":{"request":"мос","results":"10","found":"10"}},"featureMember":[{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Россия, Москва, Московский","kind":"locality","Address":{"country_code":"RU","formatted":"Россия, Москва, Московский","Components":[{"kind":"country","name":"Россия"},{"kind":"province","name":"Центральный федеральный округ"},{"kind":"province","name":"Москва"},{"kind":"area","name":"Новомосковский административный округ"},{"kind":"area","name":"поселение Московский"},{"kind":"locality","name":"Московский"}]},"AddressDetails":{"Country":{"AddressLine":"Россия, Москва, Московский","CountryNameCode":"RU","CountryName":"Россия","AdministrativeArea":{"AdministrativeAreaName":"Москва","SubAdministrativeArea":{"SubAdministrativeAreaName":"Новомосковский административный округ","Locality":{"LocalityName":"Московский"}}}}}}},"name":"Московский","description":"Москва, Россия","boundedBy":{"Envelope":{"lowerCorner":"37.337244 55.580375","upperCorner":"37.382753 55.613697"}},"Point":{"pos":"37.346551 55.602149"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"залив Московский","kind":"hydro","Address":{"formatted":"залив Московский","Components":[{"kind":"hydro","name":"залив Московский"}]},"AddressDetails":{"Country":{"AddressLine":"залив Московский","CountryName":"залив Московский"}}}},"name":"залив Московский","boundedBy":{"Envelope":{"lowerCorner":"-0.970369 -71.858219","upperCorner":"0.103306 -71.395434"}},"Point":{"pos":"-0.476772 -71.544031"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Россия, Тюменский район, посёлок Московский","kind":"locality","Address":{"country_code":"RU","formatted":"Россия, Тюменский район, посёлок Московский","Components":[{"kind":"country","name":"Россия"},{"kind":"province","name":"Уральский федеральный округ"},{"kind":"province","name":"Тюменская область"},{"kind":"area","name":"Тюменский район"},{"kind":"locality","name":"посёлок Московский"}]},"AddressDetails":{"Country":{"AddressLine":"Россия, Тюменский район, посёлок Московский","CountryNameCode":"RU","CountryName":"Россия","AdministrativeArea":{"AdministrativeAreaName":"Тюменская область","SubAdministrativeArea":{"SubAdministrativeAreaName":"Тюменский район","Locality":{"LocalityName":"посёлок Московский"}}}}}}},"name":"посёлок Московский","description":"Тюменский район, Россия","boundedBy":{"Envelope":{"lowerCorner":"65.39994 57.09716","upperCorner":"65.460316 57.119493"}},"Point":{"pos":"65.432423 57.108844"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"street","text":"Россия, Санкт-Петербург, Московский проспект","kind":"street","Address":{"country_code":"RU","formatted":"Россия, Санкт-Петербург, Московский проспект","Components":[{"kind":"country","name":"Россия"},{"kind":"province","name":"Северо-Западный федеральный округ"},{"kind":"province","name":"Санкт-Петербург"},{"kind":"locality","name":"Санкт-Петербург"},{"kind":"street","name":"Московский проспект"}]},"AddressDetails":{"Country":{"AddressLine":"Россия, Санкт-Петербург, Московский проспект","CountryNameCode":"RU","CountryName":"Россия","AdministrativeArea":{"AdministrativeAreaName":"Санкт-Петербург","Locality":{"LocalityName":"Санкт-Петербург","Thoroughfare":{"ThoroughfareName":"Московский проспект"}}}}}}},"name":"Московский проспект","description":"Санкт-Петербург, Россия","boundedBy":{"Envelope":{"lowerCorner":"30.317386 59.844285","upperCorner":"30.32239 59.926553"}},"Point":{"pos":"30.320072 59.885431"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Фиджи, Истерн, Мос","kind":"other","Address":{"country_code":"FJ","formatted":"Фиджи, Истерн, Мос","Components":[{"kind":"country","name":"Фиджи"},{"kind":"area","name":"Истерн"},{"kind":"other","name":"Мос"}]},"AddressDetails":{"Country":{"AddressLine":"Фиджи, Истерн, Мос","CountryNameCode":"FJ","CountryName":"Фиджи","AdministrativeArea":{"AdministrativeAreaName":"Истерн","Locality":{"Premise":{"PremiseName":"Мос"}}}}}}},"name":"Мос","description":"Истерн, Фиджи","boundedBy":{"Envelope":{"lowerCorner":"-178.519492 -18.674293","upperCorner":"-178.483488 -18.636291"}},"Point":{"pos":"-178.502478 -18.653538"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Беларусь, Минск, Московский район","kind":"district","Address":{"country_code":"BY","formatted":"Беларусь, Минск, Московский район","Components":[{"kind":"country","name":"Беларусь"},{"kind":"province","name":"Минск"},{"kind":"locality","name":"Минск"},{"kind":"district","name":"Московский район"}]},"AddressDetails":{"Country":{"AddressLine":"Беларусь, Минск, Московский район","CountryNameCode":"BY","CountryName":"Беларусь","AdministrativeArea":{"AdministrativeAreaName":"Минск","Locality":{"LocalityName":"Минск","DependentLocality":{"DependentLocalityName":"Московский район"}}}}}}},"name":"Московский район","description":"Минск, Беларусь","boundedBy":{"Envelope":{"lowerCorner":"27.419313 53.835489","upperCorner":"27.553585 53.907031"}},"Point":{"pos":"27.492966 53.871021"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Россия, Санкт-Петербург, Московский район","kind":"district","Address":{"country_code":"RU","formatted":"Россия, Санкт-Петербург, Московский район","Components":[{"kind":"country","name":"Россия"},{"kind":"province","name":"Северо-Западный федеральный округ"},{"kind":"province","name":"Санкт-Петербург"},{"kind":"locality","name":"Санкт-Петербург"},{"kind":"district","name":"Московский район"}]},"AddressDetails":{"Country":{"AddressLine":"Россия, Санкт-Петербург, Московский район","CountryNameCode":"RU","CountryName":"Россия","AdministrativeArea":{"AdministrativeAreaName":"Санкт-Петербург","Locality":{"LocalityName":"Санкт-Петербург","DependentLocality":{"DependentLocalityName":"Московский район"}}}}}}},"name":"Московский район","description":"Санкт-Петербург, Россия","boundedBy":{"Envelope":{"lowerCorner":"30.198979 59.744315","upperCorner":"30.38114 59.912955"}},"Point":{"pos":"30.323073 59.852176"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Россия, Чувашская Республика, городской округ Чебоксары, Московский район","kind":"district","Address":{"country_code":"RU","formatted":"Россия, Чувашская Республика, городской округ Чебоксары, Московский район","Components":[{"kind":"country","name":"Россия"},{"kind":"province","name":"Приволжский федеральный округ"},{"kind":"province","name":"Чувашская Республика"},{"kind":"area","name":"городской округ Чебоксары"},{"kind":"district","name":"Московский район"}]},"AddressDetails":{"Country":{"AddressLine":"Россия, Чувашская Республика, городской округ Чебоксары, Московский район","CountryNameCode":"RU","CountryName":"Россия","AdministrativeArea":{"AdministrativeAreaName":"Чувашская Республика","SubAdministrativeArea":{"SubAdministrativeAreaName":"городской округ Чебоксары","Locality":{"DependentLocality":{"DependentLocalityName":"Московский район"}}}}}}}},"name":"Московский район","description":"городской округ Чебоксары, Чувашская Республика, Россия","boundedBy":{"Envelope":{"lowerCorner":"47.047233 56.077167","upperCorner":"47.428424 56.298857"}},"Point":{"pos":"47.196111 56.136126"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Беларусь, Брест, Московский район","kind":"district","Address":{"country_code":"BY","formatted":"Беларусь, Брест, Московский район","Components":[{"kind":"country","name":"Беларусь"},{"kind":"province","name":"Брестская область"},{"kind":"locality","name":"Брест"},{"kind":"district","name":"Московский район"}]},"AddressDetails":{"Country":{"AddressLine":"Беларусь, Брест, Московский район","CountryNameCode":"BY","CountryName":"Беларусь","AdministrativeArea":{"AdministrativeAreaName":"Брестская область","Locality":{"LocalityName":"Брест","DependentLocality":{"DependentLocalityName":"Московский район"}}}}}}},"name":"Московский район","description":"Брест, Беларусь","boundedBy":{"Envelope":{"lowerCorner":"23.64108 52.023911","upperCorner":"23.852669 52.130329"}},"Point":{"pos":"23.747854 52.08796"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"Узбекистан, Андижанская область, Шахрихан","kind":"locality","Address":{"country_code":"UZ","formatted":"Узбекистан, Андижанская область, Шахрихан","Components":[{"kind":"country","name":"Узбекистан"},{"kind":"province","name":"Андижанская область"},{"kind":"area","name":"Шахриханский район"},{"kind":"locality","name":"Шахрихан"}]},"AddressDetails":{"Country":{"AddressLine":"Узбекистан, Андижанская область, Шахрихан","CountryNameCode":"UZ","CountryName":"Узбекистан","AdministrativeArea":{"AdministrativeAreaName":"Андижанская область","SubAdministrativeArea":{"SubAdministrativeAreaName":"Шахриханский район","Locality":{"LocalityName":"Шахрихан"}}}}}}},"name":"Шахрихан","description":"Андижанская область, Узбекистан","boundedBy":{"Envelope":{"lowerCorner":"72.010912 40.68399","upperCorner":"72.085238 40.732341"}},"Point":{"pos":"72.050949 40.711605"}}}]}}}';
    }

    // https://geocode-maps.yandex.ru/1.x/?apikey=5e79fa86-4796-4141-bab2-95ca5c74b322&geocode=%D0%BC%D0%BE%D1%81
}
