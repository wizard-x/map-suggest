<?php namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Repository\Interfaces\DTOStorageInterface;
use App\Repository\Redis\MapPointRedisRepository;
use App\Tools\DTO\MapsDataDTO;

class RepositoryTest extends KernelTestCase {

    public function setUp(): void {
        $kernel = self::bootKernel([
            'debug' => false
        ]);
    }

    public function test_redis_repository_has_save_from_dto_interface(): void {
        $redis_service = self::$container->get('snc_redis.default');
        $mapPointRedisRepository = new MapPointRedisRepository($redis_service);
        $this->assertTrue($mapPointRedisRepository instanceof DTOStorageInterface);
    }

    public function test_redis_repository_save(): void {
        $redis_service = self::$container->get('snc_redis.default');
        $mapPointRedisRepository = new MapPointRedisRepository($redis_service);
        $dto = new MapsDataDTO();
        $dto->setRequest('msk');
        $dto->setData([
            [
                'text'=>'msk',
                'point' => [1, 2]
            ],
        ]);

        try {
            $mapPointRedisRepository->saveFromDTO($dto);
            $result = $mapPointRedisRepository->retrieveAsDTOByKey('msk');
            $this->assertInstanceOf(MapsDataDTO::class, $result);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
        $this->assertSame('msk', $result->getRequest());

        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        $mapPoint = $data[0];
        $this->assertSame(1, $mapPoint['point'][0]);
        $this->assertSame(2, $mapPoint['point'][1]);
    }
}
