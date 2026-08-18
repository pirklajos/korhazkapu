<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Institution;
use App\Entity\InstitutionTheme;
use App\Entity\Membership;
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
}
