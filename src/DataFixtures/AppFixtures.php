<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Institution;
use App\Entity\InstitutionTheme;
use App\Entity\Announcement;
use App\Entity\ContentStatus;
use App\Entity\InformationPage;
use App\Entity\JourneyStep;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use App\Entity\Membership;
use App\Entity\Building;
use App\Entity\ContactPoint;
use App\Entity\Department;
use App\Entity\Floor;
use App\Entity\Room;
use App\Entity\Service;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        if ($_ENV['APP_ENV'] === 'prod') throw new \RuntimeException('Demo fixtures must never be loaded in production.');

        $epc = (new Institution('DEMO Észak-Pesti Centrumkórház', 'DEMO ÉPC-HK', 'demo-epc-hk', '1134 Budapest, Róbert Károly körút 44.'))
            ->setPrimaryDomain('demo-epc.localhost')->setDomains(['epc.demo.localhost'])->setPhone('+36 1 465 1800')->setEmail('info@demo-epc.invalid')->setMaintainerName('DEMO fenntartó');
        (new InstitutionTheme($epc))->setPrimaryColor('#005A70')->setSecondaryColor('#DCEFF3')->setAccentColor('#F2A900');

        $command = (new Institution('DEMO Duna-parti Vezényelt Kórház', 'DEMO DVK', 'demo-duna', '2400 Dunaújváros, Gyógyítás tér 1.'))
            ->setPrimaryDomain('demo-duna.localhost')->setDomains(['duna.demo.localhost'])->setPhone('+36 25 555 100')->setEmail('info@demo-duna.invalid')->setMaintainerName('DEMO vezényelt intézmény');
        (new InstitutionTheme($command))->setPrimaryColor('#5B2C83')->setSecondaryColor('#EFE7F6')->setAccentColor('#2A9D8F');

        $manager->persist($epc); $manager->persist($command);
        $this->loadStructure($manager, $epc, 'Észak-Pesti');
        $this->loadStructure($manager, $command, 'Duna-parti');

        $platform = $this->user('platform.admin@demo.invalid', 'DEMO platformadmin', ['ROLE_PLATFORM_ADMIN']);
        $manager->persist($platform);

        $roleUsers = [
            'ROLE_INSTITUTION_ADMIN' => 'institution.admin', 'ROLE_SITE_ADMIN' => 'site.admin',
            'ROLE_EDITOR' => 'editor', 'ROLE_MEDICAL_REVIEWER' => 'medical.reviewer',
            'ROLE_PUBLISHER' => 'publisher', 'ROLE_AUDITOR' => 'auditor',
        ];
        foreach ($roleUsers as $role => $localPart) {
            $user = $this->user($localPart.'@demo.invalid', 'DEMO '.$localPart, []);
            $manager->persist($user);
            $manager->persist(new Membership($user, $epc, [$role]));
        }

        $secondAdmin = $this->user('institution.admin@demo-duna.invalid', 'DEMO Duna admin', []);
        $manager->persist($secondAdmin);
        $manager->persist(new Membership($secondAdmin, $command, ['ROLE_INSTITUTION_ADMIN']));
        $manager->flush();
    }

    /** @param list<string> $platformRoles */
    private function user(string $email, string $name, array $platformRoles): User
    {
        $user = (new User($email, $name))->setPlatformRoles($platformRoles);
        return $user->setPassword($this->passwordHasher->hashPassword($user, 'Demo-Only-ChangeMe-2026!'));
    }

    private function loadStructure(ObjectManager $manager, Institution $institution, string $prefix): void
    {
        $main = new Site($institution, $prefix.' központi telephely', 'kozponti-telephely', $institution->getCentralAddress());
        $main->setAccessibility('Akadálymentes főbejárat és lift elérhető.')->setMapUrl('https://www.openstreetmap.org/');
        $outpatient = new Site($institution, $prefix.' járóbeteg központ', 'jarobeteg-kozpont', 'DEMO cím, Szakrendelő utca 2.');
        $manager->persist($main); $manager->persist($outpatient);

        $buildings = [new Building($institution, $main, 'A épület'), new Building($institution, $main, 'B diagnosztikai épület'), new Building($institution, $outpatient, 'Szakrendelő épület')];
        foreach ($buildings as $index => $building) { $building->setCode(chr(65 + $index)); $manager->persist($building); }
        $floor = (new Floor($institution, $buildings[0], 'Földszint'))->setLevelNumber(0); $manager->persist($floor);
        $room = (new Room($institution, $floor, 'Betegirányítás'))->setNumber('001'); $manager->persist($room);

        $departmentNames = ['Kardiológiai Osztály', 'Képalkotó Diagnosztika', 'Járóbeteg Szakrendelések'];
        $departments = [];
        foreach ($departmentNames as $index => $name) { $department = new Department($institution, $name, ['kardiologia','kepalkoto-diagnosztika','jarobeteg-szakrendelesek'][$index]); $department->setSummary('DEMO szervezeti egység betegtájékoztató adatokkal.'); $manager->persist($department); $departments[] = $department; }

        $serviceNames = ['Kardiológiai szakrendelés', 'EKG vizsgálat', 'Ultrahang diagnosztika', 'Laboratóriumi mintavétel', 'Betegirányítás'];
        $services = [];
        foreach ($serviceNames as $index => $name) {
            $service = (new Service($institution, $name, ['kardiologiai-szakrendeles','ekg-vizsgalat','ultrahang-diagnosztika','laboratoriumi-mintavetel','betegiranyitas'][$index]))
                ->setSummary('DEMO szolgáltatás. Részletes tájékoztatás az M2 tartalmi modulban.')
                ->setDepartment($departments[$index % count($departments)])->setSite($index < 3 ? $main : $outpatient);
            if ($index === 1) $service->setBuilding($buildings[0])->setFloor($floor)->setRoom($room)->setLocationDirections('A főbejáraton belépve forduljon jobbra, majd kövesse a kék EKG jelzéseket a 001-es helyiségig.');
            if ($index === 4) $service->setBuilding($buildings[0])->setFloor($floor)->setRoom($room)->setLocationDirections('A főbejárattól jobbra, közvetlenül az információs pult mellett.');
            $manager->persist($service);
            $services[] = $service;
            $manager->persist((new ContactPoint($institution, 'Információ', 'phone', $institution->getPhone() ?? '+36 1 000 0000'))->setService($service)->setAvailability('Munkanapokon 8:00–16:00'));
        }

        $pageTitles = ['Érkezés a vizsgálatra', 'Mit hozzon magával?', 'Beteglátogatási rend', 'Akadálymentes megközelítés', 'Leletátvételi tudnivalók'];
        foreach ($pageTitles as $index => $title) {
            $page = (new InformationPage($institution, $title, ['erkezes-a-vizsgalatra','mit-hozzon-magaval','beteglatogatasi-rend','akadalymentes-megkozelites','leletatveteli-tudnivalok'][$index]))
                ->setSummary('DEMO betegtájékoztató összefoglaló.')->setCategory('betegtajekoztatas')->setTags(['demo','beteginfo'])
                ->setContent(['blocks' => [['type' => 'text', 'text' => 'Ez egy biztonságos, strukturált demo tartalom.']]])
                ->setStatus(ContentStatus::Published)->setPublicationWindow(new \DateTimeImmutable('-1 day'), null);
            $manager->persist($page);
        }

        $guide = (new ProcedureGuide($institution, 'EKG vizsgálat előtti tudnivalók', 'ekg-vizsgalat-elott'))
            ->setSummary('Rövid, közérthető DEMO felkészülési útmutató.')->setService($services[1])->setDurationMinutes(20)
            ->setPurpose('A szív elektromos működésének vizsgálata.')->setPreparation(['fasting' => false, 'medicationWarning' => 'Gyógyszert csak orvosi utasításra módosítson.'])
            ->setRequiredDocuments(['személyazonosító okmány', 'beutaló, ha rendelkezésre áll'])->setStatus(ContentStatus::Published)->setPublicationWindow(new \DateTimeImmutable('-1 day'), null);
        $manager->persist($guide);

        foreach ([['Első ambuláns vizit','elso-ambulans-vizit'],['Diagnosztikai vizsgálat napja','diagnosztikai-vizsgalat-napja']] as [$title,$slug]) {
            $journey=(new PatientJourney($institution,$title,$slug))->setSummary('DEMO betegút lépésről lépésre.')->setTargetAudience('Első alkalommal érkező betegek')->setStatus(ContentStatus::Published)->setPublicationWindow(new \DateTimeImmutable('-1 day'),null);
            $manager->persist($journey); $manager->persist(new JourneyStep($institution,$journey,1,'Érkezés','Jelentkezzen a betegirányításnál.')); $manager->persist(new JourneyStep($institution,$journey,2,'Vizsgálat','Kövesse a kijelzett és személyes útmutatást.'));
        }

        $active=(new Announcement($institution,'Megváltozott bejárat','megvaltozott-bejarat'))->setSummary('DEMO közlemény')->setBody('A főbejárat felújítás miatt ideiglenesen más útvonalon érhető el.')->setType('warning')->setPriority(10)->setStatus(ContentStatus::Published)->setPublicationWindow(new \DateTimeImmutable('-1 day'),new \DateTimeImmutable('+14 days'));
        $expired=(new Announcement($institution,'Korábbi parkolási korlátozás','korabbi-parkolasi-korlatozas'))->setBody('Lejárt DEMO közlemény.')->setStatus(ContentStatus::Published)->setPublicationWindow(new \DateTimeImmutable('-10 days'),new \DateTimeImmutable('-1 day'));
        $manager->persist($active); $manager->persist($expired);
    }
}
