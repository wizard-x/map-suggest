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

        $response = $yandexMapsApi->search('????????');
        $this->assertInstanceOf(MapsDataDTO::class, $response);
    }


    public function get_fake_search_data(): string {
        return '{"response":{"GeoObjectCollection":{"metaDataProperty":{"GeocoderResponseMetaData":{"request":"??????","results":"10","found":"10"}},"featureMember":[{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"????????????, ????????????, ????????????????????","kind":"locality","Address":{"country_code":"RU","formatted":"????????????, ????????????, ????????????????????","Components":[{"kind":"country","name":"????????????"},{"kind":"province","name":"?????????????????????? ?????????????????????? ??????????"},{"kind":"province","name":"????????????"},{"kind":"area","name":"???????????????????????????? ???????????????????????????????? ??????????"},{"kind":"area","name":"?????????????????? ????????????????????"},{"kind":"locality","name":"????????????????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????, ????????????, ????????????????????","CountryNameCode":"RU","CountryName":"????????????","AdministrativeArea":{"AdministrativeAreaName":"????????????","SubAdministrativeArea":{"SubAdministrativeAreaName":"???????????????????????????? ???????????????????????????????? ??????????","Locality":{"LocalityName":"????????????????????"}}}}}}},"name":"????????????????????","description":"????????????, ????????????","boundedBy":{"Envelope":{"lowerCorner":"37.337244 55.580375","upperCorner":"37.382753 55.613697"}},"Point":{"pos":"37.346551 55.602149"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"?????????? ????????????????????","kind":"hydro","Address":{"formatted":"?????????? ????????????????????","Components":[{"kind":"hydro","name":"?????????? ????????????????????"}]},"AddressDetails":{"Country":{"AddressLine":"?????????? ????????????????????","CountryName":"?????????? ????????????????????"}}}},"name":"?????????? ????????????????????","boundedBy":{"Envelope":{"lowerCorner":"-0.970369 -71.858219","upperCorner":"0.103306 -71.395434"}},"Point":{"pos":"-0.476772 -71.544031"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"????????????, ?????????????????? ??????????, ?????????????? ????????????????????","kind":"locality","Address":{"country_code":"RU","formatted":"????????????, ?????????????????? ??????????, ?????????????? ????????????????????","Components":[{"kind":"country","name":"????????????"},{"kind":"province","name":"?????????????????? ?????????????????????? ??????????"},{"kind":"province","name":"?????????????????? ??????????????"},{"kind":"area","name":"?????????????????? ??????????"},{"kind":"locality","name":"?????????????? ????????????????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????, ?????????????????? ??????????, ?????????????? ????????????????????","CountryNameCode":"RU","CountryName":"????????????","AdministrativeArea":{"AdministrativeAreaName":"?????????????????? ??????????????","SubAdministrativeArea":{"SubAdministrativeAreaName":"?????????????????? ??????????","Locality":{"LocalityName":"?????????????? ????????????????????"}}}}}}},"name":"?????????????? ????????????????????","description":"?????????????????? ??????????, ????????????","boundedBy":{"Envelope":{"lowerCorner":"65.39994 57.09716","upperCorner":"65.460316 57.119493"}},"Point":{"pos":"65.432423 57.108844"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"street","text":"????????????, ??????????-??????????????????, ???????????????????? ????????????????","kind":"street","Address":{"country_code":"RU","formatted":"????????????, ??????????-??????????????????, ???????????????????? ????????????????","Components":[{"kind":"country","name":"????????????"},{"kind":"province","name":"????????????-???????????????? ?????????????????????? ??????????"},{"kind":"province","name":"??????????-??????????????????"},{"kind":"locality","name":"??????????-??????????????????"},{"kind":"street","name":"???????????????????? ????????????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????, ??????????-??????????????????, ???????????????????? ????????????????","CountryNameCode":"RU","CountryName":"????????????","AdministrativeArea":{"AdministrativeAreaName":"??????????-??????????????????","Locality":{"LocalityName":"??????????-??????????????????","Thoroughfare":{"ThoroughfareName":"???????????????????? ????????????????"}}}}}}},"name":"???????????????????? ????????????????","description":"??????????-??????????????????, ????????????","boundedBy":{"Envelope":{"lowerCorner":"30.317386 59.844285","upperCorner":"30.32239 59.926553"}},"Point":{"pos":"30.320072 59.885431"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"??????????, ????????????, ??????","kind":"other","Address":{"country_code":"FJ","formatted":"??????????, ????????????, ??????","Components":[{"kind":"country","name":"??????????"},{"kind":"area","name":"????????????"},{"kind":"other","name":"??????"}]},"AddressDetails":{"Country":{"AddressLine":"??????????, ????????????, ??????","CountryNameCode":"FJ","CountryName":"??????????","AdministrativeArea":{"AdministrativeAreaName":"????????????","Locality":{"Premise":{"PremiseName":"??????"}}}}}}},"name":"??????","description":"????????????, ??????????","boundedBy":{"Envelope":{"lowerCorner":"-178.519492 -18.674293","upperCorner":"-178.483488 -18.636291"}},"Point":{"pos":"-178.502478 -18.653538"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"????????????????, ??????????, ???????????????????? ??????????","kind":"district","Address":{"country_code":"BY","formatted":"????????????????, ??????????, ???????????????????? ??????????","Components":[{"kind":"country","name":"????????????????"},{"kind":"province","name":"??????????"},{"kind":"locality","name":"??????????"},{"kind":"district","name":"???????????????????? ??????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????????, ??????????, ???????????????????? ??????????","CountryNameCode":"BY","CountryName":"????????????????","AdministrativeArea":{"AdministrativeAreaName":"??????????","Locality":{"LocalityName":"??????????","DependentLocality":{"DependentLocalityName":"???????????????????? ??????????"}}}}}}},"name":"???????????????????? ??????????","description":"??????????, ????????????????","boundedBy":{"Envelope":{"lowerCorner":"27.419313 53.835489","upperCorner":"27.553585 53.907031"}},"Point":{"pos":"27.492966 53.871021"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"????????????, ??????????-??????????????????, ???????????????????? ??????????","kind":"district","Address":{"country_code":"RU","formatted":"????????????, ??????????-??????????????????, ???????????????????? ??????????","Components":[{"kind":"country","name":"????????????"},{"kind":"province","name":"????????????-???????????????? ?????????????????????? ??????????"},{"kind":"province","name":"??????????-??????????????????"},{"kind":"locality","name":"??????????-??????????????????"},{"kind":"district","name":"???????????????????? ??????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????, ??????????-??????????????????, ???????????????????? ??????????","CountryNameCode":"RU","CountryName":"????????????","AdministrativeArea":{"AdministrativeAreaName":"??????????-??????????????????","Locality":{"LocalityName":"??????????-??????????????????","DependentLocality":{"DependentLocalityName":"???????????????????? ??????????"}}}}}}},"name":"???????????????????? ??????????","description":"??????????-??????????????????, ????????????","boundedBy":{"Envelope":{"lowerCorner":"30.198979 59.744315","upperCorner":"30.38114 59.912955"}},"Point":{"pos":"30.323073 59.852176"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"????????????, ?????????????????? ????????????????????, ?????????????????? ?????????? ??????????????????, ???????????????????? ??????????","kind":"district","Address":{"country_code":"RU","formatted":"????????????, ?????????????????? ????????????????????, ?????????????????? ?????????? ??????????????????, ???????????????????? ??????????","Components":[{"kind":"country","name":"????????????"},{"kind":"province","name":"?????????????????????? ?????????????????????? ??????????"},{"kind":"province","name":"?????????????????? ????????????????????"},{"kind":"area","name":"?????????????????? ?????????? ??????????????????"},{"kind":"district","name":"???????????????????? ??????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????, ?????????????????? ????????????????????, ?????????????????? ?????????? ??????????????????, ???????????????????? ??????????","CountryNameCode":"RU","CountryName":"????????????","AdministrativeArea":{"AdministrativeAreaName":"?????????????????? ????????????????????","SubAdministrativeArea":{"SubAdministrativeAreaName":"?????????????????? ?????????? ??????????????????","Locality":{"DependentLocality":{"DependentLocalityName":"???????????????????? ??????????"}}}}}}}},"name":"???????????????????? ??????????","description":"?????????????????? ?????????? ??????????????????, ?????????????????? ????????????????????, ????????????","boundedBy":{"Envelope":{"lowerCorner":"47.047233 56.077167","upperCorner":"47.428424 56.298857"}},"Point":{"pos":"47.196111 56.136126"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"????????????????, ??????????, ???????????????????? ??????????","kind":"district","Address":{"country_code":"BY","formatted":"????????????????, ??????????, ???????????????????? ??????????","Components":[{"kind":"country","name":"????????????????"},{"kind":"province","name":"?????????????????? ??????????????"},{"kind":"locality","name":"??????????"},{"kind":"district","name":"???????????????????? ??????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????????, ??????????, ???????????????????? ??????????","CountryNameCode":"BY","CountryName":"????????????????","AdministrativeArea":{"AdministrativeAreaName":"?????????????????? ??????????????","Locality":{"LocalityName":"??????????","DependentLocality":{"DependentLocalityName":"???????????????????? ??????????"}}}}}}},"name":"???????????????????? ??????????","description":"??????????, ????????????????","boundedBy":{"Envelope":{"lowerCorner":"23.64108 52.023911","upperCorner":"23.852669 52.130329"}},"Point":{"pos":"23.747854 52.08796"}}},{"GeoObject":{"metaDataProperty":{"GeocoderMetaData":{"precision":"other","text":"????????????????????, ?????????????????????? ??????????????, ????????????????","kind":"locality","Address":{"country_code":"UZ","formatted":"????????????????????, ?????????????????????? ??????????????, ????????????????","Components":[{"kind":"country","name":"????????????????????"},{"kind":"province","name":"?????????????????????? ??????????????"},{"kind":"area","name":"???????????????????????? ??????????"},{"kind":"locality","name":"????????????????"}]},"AddressDetails":{"Country":{"AddressLine":"????????????????????, ?????????????????????? ??????????????, ????????????????","CountryNameCode":"UZ","CountryName":"????????????????????","AdministrativeArea":{"AdministrativeAreaName":"?????????????????????? ??????????????","SubAdministrativeArea":{"SubAdministrativeAreaName":"???????????????????????? ??????????","Locality":{"LocalityName":"????????????????"}}}}}}},"name":"????????????????","description":"?????????????????????? ??????????????, ????????????????????","boundedBy":{"Envelope":{"lowerCorner":"72.010912 40.68399","upperCorner":"72.085238 40.732341"}},"Point":{"pos":"72.050949 40.711605"}}}]}}}';
    }

    // https://geocode-maps.yandex.ru/1.x/?apikey=5e79fa86-4796-4141-bab2-95ca5c74b322&geocode=%D0%BC%D0%BE%D1%81
}
