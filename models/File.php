<?php

namespace ssv_file_management\models;

class File extends Item
{
    private $fileType;

    public function __construct(ItemInterface $parentId, string $id, string $name)
    {
        parent::__construct($parentId, $id, $name);
    }


    public function getFileType(): string {
        return $this->fileType;
    }
}
