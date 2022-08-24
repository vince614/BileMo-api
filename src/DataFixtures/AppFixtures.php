<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    /** @var UserPasswordHasherInterface  */
    private $passwordHasher;

    /**
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Load fixture
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin_user = new User();
        $admin_user
            ->setEmail('admin@bilemo.com')
            ->setPassword($this->passwordHasher->hashPassword($admin_user, 'admin'))
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setUsername('admin');
        $manager->persist($admin_user);

        // Create classic user
        $classic_user = new User();
        $classic_user
            ->setEmail('user@bilemo.com')
            ->setPassword($this->passwordHasher->hashPassword($admin_user, 'user'))
            ->setRoles(['ROLE_USER'])
            ->setUsername('user');
        $manager->persist($classic_user);
        $manager->flush();
    }
}
