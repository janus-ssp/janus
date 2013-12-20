<?php
namespace Janus\Connection\Metadata;

interface FieldConfigInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getChildren();
}