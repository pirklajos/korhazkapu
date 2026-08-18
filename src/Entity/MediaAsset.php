<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'media_asset')]
class MediaAsset extends AbstractTenantEntity
{
    #[ORM\Column(length:255)] private string $originalName; #[ORM\Column(length:500)] private string $storagePath; #[ORM\Column(length:120)] private string $mimeType; #[ORM\Column] private int $sizeBytes; #[ORM\Column(length:500)] private string $altText;
    public function __construct(Institution $institution,string $originalName,string $storagePath,string $mimeType,int $sizeBytes,string $altText){parent::__construct($institution);$this->originalName=$originalName;$this->storagePath=$storagePath;$this->mimeType=$mimeType;$this->sizeBytes=$sizeBytes;$this->altText=$altText;}
    public function getOriginalName():string{return $this->originalName;} public function getStoragePath():string{return $this->storagePath;} public function getMimeType():string{return $this->mimeType;} public function getSizeBytes():int{return $this->sizeBytes;} public function getAltText():string{return $this->altText;}
}
