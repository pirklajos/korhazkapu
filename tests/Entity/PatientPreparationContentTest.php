<?php
declare(strict_types=1);
namespace App\Tests\Entity;

use App\Entity\Institution;
use App\Entity\JourneyStep;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use PHPUnit\Framework\TestCase;

final class PatientPreparationContentTest extends TestCase
{
    public function testJourneyKeepsOrderedStepDetails():void
    {
        $institution=new Institution('Teszt Kórház','TK','teszt-korhaz','Budapest');$journey=new PatientJourney($institution,'Érkezés','erkezes');$step=(new JourneyStep($institution,$journey,1,'Regisztráció','Jelentkezzen a pultnál.'))->setLocation('Földszint')->setAction('Készítse elő az iratait.')->setRequiredDocuments(['személyi igazolvány']);
        self::assertSame($journey,$step->getJourney());self::assertSame('Földszint',$step->getLocation());self::assertSame(['személyi igazolvány'],$step->getRequiredDocuments());self::assertCount(1,$journey->getSteps());
    }

    public function testProcedureGuideStoresStructuredPreparation():void
    {
        $institution=new Institution('Teszt Kórház','TK','teszt-korhaz','Budapest');$guide=(new ProcedureGuide($institution,'Labor','labor'))->setDiscomfort('Enyhe kellemetlenség')->setPreparation(['fasting'=>true,'steps'=>['Ne reggelizzen']])->setRequiredDocuments(['TAJ-kártya'])->setReferralRequired(true)->setAftercare(['steps'=>['Pihenjen']]);
        self::assertTrue($guide->isReferralRequired());self::assertTrue($guide->getPreparation()['fasting']);self::assertSame(['TAJ-kártya'],$guide->getRequiredDocuments());self::assertSame(['Pihenjen'],$guide->getAftercare()['steps']);
    }
}
