<?php
declare(strict_types=1);
namespace App\Tests\Entity;

use App\Entity\Institution;
use App\Entity\Building;
use App\Entity\Floor;
use App\Entity\JourneyStep;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use App\Entity\Room;
use App\Entity\Site;
use PHPUnit\Framework\TestCase;

final class PatientPreparationContentTest extends TestCase
{
    public function testJourneyKeepsOrderedStepDetails():void
    {
        $institution=new Institution('Teszt Kórház','TK','teszt-korhaz','Budapest');$site=new Site($institution,'Központ','kozpont','Budapest');$building=new Building($institution,$site,'A épület');$floor=new Floor($institution,$building,'Földszint');$room=new Room($institution,$floor,'Betegirányítás');$journey=new PatientJourney($institution,'Érkezés','erkezes');$step=(new JourneyStep($institution,$journey,1,'Regisztráció','Jelentkezzen a pultnál.'))->setBuilding($building)->setFloor($floor)->setRoom($room)->setLocation('A főbejárattól jobbra')->setAction('Készítse elő az iratait.')->setRequiredDocuments(['személyi igazolvány']);
        self::assertSame($journey,$step->getJourney());self::assertSame($building,$step->getBuilding());self::assertSame($floor,$step->getFloor());self::assertSame($room,$step->getRoom());self::assertSame(['személyi igazolvány'],$step->getRequiredDocuments());self::assertCount(1,$journey->getSteps());
    }

    public function testProcedureGuideStoresStructuredPreparation():void
    {
        $institution=new Institution('Teszt Kórház','TK','teszt-korhaz','Budapest');$guide=(new ProcedureGuide($institution,'Labor','labor'))->setDiscomfort('Enyhe kellemetlenség')->setPreparation(['fasting'=>true,'steps'=>['Ne reggelizzen']])->setRequiredDocuments(['TAJ-kártya'])->setReferralRequired(true)->setAftercare(['steps'=>['Pihenjen']]);
        self::assertTrue($guide->isReferralRequired());self::assertTrue($guide->getPreparation()['fasting']);self::assertSame(['TAJ-kártya'],$guide->getRequiredDocuments());self::assertSame(['Pihenjen'],$guide->getAftercare()['steps']);
    }
}
