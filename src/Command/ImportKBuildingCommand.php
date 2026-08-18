<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Building;
use App\Entity\Department;
use App\Entity\Floor;
use App\Entity\Institution;
use App\Entity\Room;
use App\Entity\Service;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(name: 'app:import:k-building', description: 'A K épület alaprajzairól feldolgozott helyiség- és ellátásadatokat importálja.')]
final class ImportKBuildingCommand extends Command
{
    /** @var array<string, array{level:int, rooms:array<string,string>}> */
    private const FLOORS = [
        'Földszint' => ['level' => 0, 'rooms' => [
            'KFSZ 001' => 'Mammográfia – sztereotaxia',
            'KFSZ 002' => 'Mammográfia – felvétel',
            'KFSZ 003' => 'Emlő ultrahang',
            'KFSZ 004-005' => 'Gipszelő',
            'KFSZ 006' => 'Baleseti sebészeti szakambulancia',
            'KFSZ 007' => 'COVID-mintavevő',
            'KFSZ 013' => 'Ortopédiai szakrendelő',
            'KFSZ 014' => 'Baleseti sebészeti szakrendelő',
            'KFSZ 015' => 'Baleseti sebészeti szakrendelő iroda',
            'KFSZ 016' => 'Oltópont, immunológiai szakrendelő',
            'KFSZ 017,019,020' => 'Reumatológiai szakrendelő',
            'KFSZ 018' => 'Reumatológiai iroda',
            'KFSZ 021' => 'Reumatológiai szakrendelő',
            'KFSZ 022' => 'Denzitometria (csontsűrűség-vizsgáló)',
            'KFSZ 023-025' => 'I. számú felvételi részleg',
            'KFSZ 029' => 'Baleseti sebészeti szakambulancia',
            'KFSZ 030' => 'Kézsebészeti szakambulancia',
            'KFSZ 031' => 'Mammográfia',
        ]],
        'Magasföldszint' => ['level' => 0, 'rooms' => [
            'KM 001' => 'Háziorvosi rendelő',
            'KM 002' => 'Klinikai szakpszichológiai szakrendelő',
            'KM 003-005' => 'Ideggyógyászati szakrendelő',
            'KM 006-008' => 'Pszichiátriai szakrendelő',
            'KM 016' => 'Kardiológiai szakrendelő iroda',
            'KM 017' => 'Kardiológiai megfigyelő',
            'KM 018' => 'ECHO labor',
            'KM 019' => 'Kardiológiai kerékpár-ergométer vizsgáló',
            'KM 020,023' => 'Nőgyógyászati szakrendelő',
            'KM 021' => 'Nőgyógyászati iroda',
            'KM 022' => 'Baba–Mama szoba, CTG-vizsgáló',
            'KM 024' => 'Kardiológiai szakrendelő (ABPM, Holter, terheléses EKG)',
            'KM 025' => 'Kardiológiai szakrendelő',
            'KM 026' => 'Urológiai szakrendelő',
            'KM 027' => 'Urológiai iroda',
            'KM 028' => 'Urológiai szakrendelő',
            'KM 029-031' => 'EKG – kardiológiai szakrendelő',
            'KM 032' => 'Fül-orr-gégészeti szakrendelő (AB és DEFG rendelők)',
            'KM 033-034' => 'Plasztikai sebészeti műtő',
            'KM 035,036' => 'Gasztroenterológiai szakrendelő',
            'KM 037,038,039' => 'Plasztikai sebészeti szakambulancia',
            'KM 040,042' => 'Bőrgyógyászati szakrendelő',
            'KM 041' => 'Bőrgyógyászati kötöző',
        ]],
        'I. emelet' => ['level' => 1, 'rooms' => [
            'KI 001A' => 'Rendelőintézeti ügyvitel',
            'KI 001B' => 'Rendelőintézet vezető asszisztensi iroda',
            'KI 002' => 'Rendelőintézet igazgatói iroda',
            'KI 003' => 'Tudományos igazgatói iroda',
            'KI 004' => 'Speciális rendeltetésű rendelő (nem közfinanszírozott)',
            'KI 010' => 'Szemészeti szakrendelő',
            'KI 011-012' => 'Laboratórium',
            'KI 013' => 'Speciális rendeltetésű rendelő',
            'KI 014' => 'Belgyógyászati szakrendelő',
            'KI 015,017' => 'Belgyógyászati szakrendelő',
            'KI 016' => 'Belgyógyászati iroda',
            'KI 018' => 'Légzésfunkció',
            'KI 019-020' => 'Tüdőgyógyászati szakrendelő, tüdőgondozó',
            'KI 021,023' => 'Tüdőgyógyászati szakrendelő, tüdőgondozó',
            'KI 022' => 'Tüdőgyógyászati iroda',
            'KI 024' => 'Belgyógyászati szakrendelő',
            'KI 025-028' => 'Foglalkozás-egészségügyi alapellátás',
            'KI 029-030' => 'Fogászati rendelő',
            'KI 031-033' => 'Szájsebészeti szakambulancia',
            'KI 034' => 'Oktató- és tárgyalóterem',
            'KI 035-040' => 'Speciális rendeltetésű rendelő',
            'D1-051' => 'Gasztroendoszkópia',
            'D1-058-107' => 'Kardiológiai szakambulancia, EEG–EMG–ENG szakambulancia',
        ]],
        'II. emelet' => ['level' => 2, 'rooms' => [
            'K2 001-002' => 'Kiemelt általános sebészeti kezelő',
            'K2 003' => 'Szájsebészeti kis műtő',
            'K2 004' => 'Általános sebészeti szakambulancia iroda',
            'K2 005-006' => 'Általános sebészeti szakambulancia, proctológia',
            'K2 007' => 'Haemophilia–Haemostasis szakrendelő (irattár)',
            'K2 013-014' => 'Szemészeti szakrendelő és neuroophthalmológiai szakambulancia',
            'K2 015-016' => 'Égési szakambulancia',
            'K2 017' => 'Belgyógyászati–hepatológiai szakambulancia',
            'K2 018-019' => 'Belgyógyászati szakambulancia',
            'K2 020,021,022,023' => 'Diabetológiai szakrendelő',
            'K2 024' => 'Endokrinológiai szakrendelő',
            'K2 025-026' => 'Fájdalom- és aneszteziológiai szakambulancia',
            'K2 027' => 'Nephrológiai szakambulancia',
            'K2 028' => 'Háziorvosi alapellátás',
            'K2 029' => 'Diabetológiai vérvétel',
            'K2 030' => 'Diabetológiai szakrendelő',
            'K2 031' => 'Diabetológiai szakrendelő',
            'K2 032' => 'Belgyógyászati szakambulancia',
            'K2 033-038' => 'Haemophilia és Haemostasis szakrendelő',
            'K2 039-040' => 'Audiológiai és otoneurológiai szakrendelő',
            'K2 041' => 'Idegsebészeti szakrendelő',
            'K2 042' => 'Idegsebészeti szakambulancia',
            'K2 043' => 'Általános sebészeti szakambulancia',
            'K2 044' => 'Általános sebészeti szakambulancia',
            'K2 045-046' => 'Országos Porphyria Központ szakrendelő',
            'K2 047-048' => 'Általános sebészeti szakrendelő',
        ]],
    ];

    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('institution', InputArgument::OPTIONAL, 'Az intézmény slugja.', 'demo-epc-hk');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $institution = $this->em->getRepository(Institution::class)->findOneBy(['slug' => $input->getArgument('institution')]);
        if (!$institution instanceof Institution) {
            $io->error('Az intézmény nem található.');
            return Command::FAILURE;
        }

        $site = $this->em->getRepository(Site::class)->findOneBy(['institution' => $institution, 'slug' => 'kozponti-telephely']);
        if (!$site instanceof Site) {
            $io->error('A központi telephely nem található.');
            return Command::FAILURE;
        }

        $building = $this->em->getRepository(Building::class)->findOneBy(['institution' => $institution, 'code' => 'K']);
        if (!$building instanceof Building) {
            $building = (new Building($institution, $site, 'K épület'))->setCode('K');
            $this->em->persist($building);
        }

        $department = $this->em->getRepository(Department::class)->findOneBy(['institution' => $institution, 'slug' => 'jarobeteg-szakrendelo-intezet']);
        if (!$department instanceof Department) {
            $department = (new Department($institution, 'Járóbeteg Szakrendelő Intézet', 'jarobeteg-szakrendelo-intezet'))
                ->setSummary('A K épület szakrendeléseit és ambulanciáit összefogó szervezeti egység.');
            $this->em->persist($department);
        }

        $slugger = new AsciiSlugger('hu');
        $roomCount = 0;
        $serviceCount = 0;
        foreach (self::FLOORS as $floorName => $floorData) {
            $floor = $this->em->getRepository(Floor::class)->findOneBy(['institution' => $institution, 'building' => $building, 'name' => $floorName]);
            if (!$floor instanceof Floor) {
                $floor = (new Floor($institution, $building, $floorName))->setLevelNumber($floorData['level']);
                $this->em->persist($floor);
            }

            foreach ($floorData['rooms'] as $number => $activity) {
                $room = $this->em->getRepository(Room::class)->findOneBy(['institution' => $institution, 'floor' => $floor, 'number' => $number]);
                if (!$room instanceof Room) {
                    $room = (new Room($institution, $floor, $activity))->setNumber($number);
                    $this->em->persist($room);
                    ++$roomCount;
                } else {
                    $room->setName($activity);
                }

                $slug = 'k-epulet-'.strtolower((string) $slugger->slug($number));
                $service = $this->em->getRepository(Service::class)->findOneBy(['institution' => $institution, 'slug' => $slug]);
                if (!$service instanceof Service) {
                    $service = new Service($institution, $activity, $slug);
                    $this->em->persist($service);
                    ++$serviceCount;
                }
                $service->setName($activity)
                    ->setSummary('A Járóbeteg Szakrendelő Intézet K épületében elérhető ellátás vagy ügyintézési hely.')
                    ->setDepartment($department)->setSite($site)->setBuilding($building)->setFloor($floor)->setRoom($room)
                    ->setLocationDirections(sprintf('K épület, %s, %s. A helyszínen kövesse a szobaszám és a szakrendelés jelzéseit.', $floorName, $number));
            }
        }

        $this->em->flush();
        $io->success(sprintf('A K épület importja elkészült: %d új helyiség, %d új szolgáltatás.', $roomCount, $serviceCount));
        return Command::SUCCESS;
    }
}
