<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity] #[ORM\Table(name:'search_synonym')]
class SearchSynonym extends AbstractTenantEntity
{
    #[ORM\Column(length:120)] private string $term;
    /** @var list<string> */ #[ORM\Column(type:Types::JSON)] private array $synonyms=[];
    /** @param list<string> $synonyms */ public function __construct(Institution $institution,string $term,array $synonyms=[]){parent::__construct($institution);$this->term=$term;$this->setSynonyms($synonyms);}
    public function getTerm():string{return $this->term;} public function setTerm(string $v):self{$this->term=trim($v);return $this;} /** @return list<string> */ public function getSynonyms():array{return $this->synonyms;} /** @param list<string> $v */ public function setSynonyms(array $v):self{$this->synonyms=array_values(array_unique(array_filter(array_map('trim',$v))));return $this;}
}
