<?php

namespace ssv_file_management\models;

class Item implements ItemInterface
{
    private $parentId;
    private $id;
    private $name;

    public function __construct(ItemInterface $parentId, string $id, string $name)
    {
        $this->parentId = $parentId;
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getParent() {
        return $this->parentId;
    }

    public function getPath(): string {
        return $this->getParent()->getPath() . '/' . $this->id;
    }
}
