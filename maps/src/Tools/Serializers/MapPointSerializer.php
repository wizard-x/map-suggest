<?php declare(strict_types=1);

namespace App\Tools\Serializers;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;


class MapPointSerializer {
    public function serialize($object, array $groups = []) {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizers = [new ObjectNormalizer($classMetadataFactory)];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($object, 'json', ['groups' => $groups]);
    }

    // public function deserialize($object, $class, $format) {
    //     $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    //     $normalizers = [new ObjectNormalizer($classMetadataFactory)];
    //     $encoders = [new JsonEncoder()];
    //     $serializer = new Serializer($normalizers, $encoders);

    //     $result = [];
    //     return $serializer->deserialize(json_encode($object->getData()), $class, 'json');
    // }
}