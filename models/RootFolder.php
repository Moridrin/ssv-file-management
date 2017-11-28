<?php

namespace ssv_file_management\models;

class RootFolder implements FolderInterface
{
    private $id;
    private $name;
    private $children;

    public function __construct(string $id, string $name, array $children)
    {
        $this->id = $id;
        $this->name = $name;
        $this->children = $children;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPath(): string {
        return $this->id;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
