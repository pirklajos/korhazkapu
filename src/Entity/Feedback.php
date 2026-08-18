<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity] #[ORM\Table(name:'feedback')]
class Feedback extends AbstractTenantEntity
{
    #[ORM\Column(length:500)] private string $pagePath; #[ORM\Column] private bool $helpful; #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $comment;
    public function __construct(Institution $institution,string $pagePath,bool $helpful,?string $comment){parent::__construct($institution);$this->pagePath=$pagePath;$this->helpful=$helpful;$this->comment=$comment?trim($comment):null;}
    public function getPagePath():string{return $this->pagePath;} public function isHelpful():bool{return $this->helpful;} public function getComment():?string{return $this->comment;}
}
