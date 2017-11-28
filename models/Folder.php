<?php

namespace ssv_file_management\models;

class Folder extends Item implements FolderInterface
{
    /** @var  Item[] */
    private $children;

    public function __construct(ItemInterface $parentId, string $id, string $name, array $children)
    {
        parent::__construct($parentId, $id, $name);
        $this->children = $children;
    }

    public function getChildren(): array {
        return $this->children;
    }
}
