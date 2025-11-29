<?php

namespace Project\App\HTTP;

use Project\App\AppBuilder;

/**
 * Your base web processor class
 */
abstract class Processor extends \PHPixie\DefaultBundle\HTTP\Processor
{
    /**
     * @var AppBuilder
     */
    protected $builder;

    /**
     * @param AppBuilder $builder
     */
    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    // Helper para ler parâmetro do corpo (POST) ou da query string (URL)
    protected function param($request, $key, $default = null)
    {
        $value = null;
        $data = $request->data();
        if ($data) {
            $value = $data->get($key);
        }
        if ($value === null) {
            $query = $request->query();
            if ($query) {
                $value = $query->get($key);
            }
        }
        return $value === null ? $default : $value;
    }

    // Gera UUID v4 para chaves primárias (PostgreSQL uuid)
    protected function uuid()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // versão 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variante RFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}