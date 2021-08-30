<?php namespace App\Repository\Interfaces;

interface DTOStorageInterface {
    function saveFromDTO($data);
    function retrieveAsDTOByKey($key);
}