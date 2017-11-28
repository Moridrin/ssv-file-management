<?
declare(strict_types=1);


namespace ssv_file_management\models;


interface FolderInterface extends ItemInterface
{

    public function getChildren(): array;

}
