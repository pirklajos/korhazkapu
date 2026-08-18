<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;use Doctrine\ORM\Mapping as ORM;use Symfony\Bridge\Doctrine\Types\UuidType;use Symfony\Component\Uid\Uuid;
#[ORM\Entity] #[ORM\Table(name:'content_template')]
class ContentTemplate
{
 #[ORM\Id] #[ORM\Column(type:UuidType::NAME)] private Uuid $id;#[ORM\Column(length:120,unique:true)] private string $templateKey;#[ORM\Column(length:255)] private string $title;#[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $summary;/** @var array<string,mixed> */ #[ORM\Column(type:Types::JSON)] private array $content;#[ORM\Column] private int $version;
 /** @param array<string,mixed> $content */ public function __construct(string $key,string $title,?string $summary,array $content,int $version=1){$this->id=Uuid::v7();$this->templateKey=$key;$this->title=$title;$this->summary=$summary;$this->content=$content;$this->version=$version;}public function getId():Uuid{return $this->id;}public function getTemplateKey():string{return $this->templateKey;}public function getTitle():string{return $this->title;}public function getSummary():?string{return $this->summary;}/** @return array<string,mixed> */public function getContent():array{return $this->content;}public function getVersion():int{return $this->version;}
}
