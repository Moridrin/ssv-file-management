<?
declare(strict_types=1);


namespace ssv_file_management\models;


interface ItemInterface
{

    public function getId(): string;

    public function getName(): string;

    public function getPath(): string;

}
