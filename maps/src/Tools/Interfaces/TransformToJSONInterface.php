<?php namespace App\Tools\Interfaces;

interface TransformToJSONInterface {
    public function toJSON(): string;
}